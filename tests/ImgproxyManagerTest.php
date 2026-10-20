<?php

declare(strict_types=1);

use function Safe\hex2bin;

use HosmelQ\Imgproxy\InfoUrlBuilder;
use HosmelQ\Imgproxy\Laravel\Facades\Imgproxy;
use HosmelQ\Imgproxy\ResizingType;
use HosmelQ\Imgproxy\SourceEncoding;
use HosmelQ\Imgproxy\UrlBuilder;

it('rejects invalid option aliases', function (array $aliases): void {
    config(['imgproxy.option_aliases' => $aliases]);

    Imgproxy::builder();
})->with([
    'invalid name' => [['not_an_option' => 'x']],
    'invalid value' => [['resize' => true]],
])->throws(InvalidArgumentException::class);

it('rejects unknown source encodings', function (): void {
    config(['imgproxy.source_encoding' => 'unknown']);

    Imgproxy::builder();
})->throws(
    InvalidArgumentException::class,
    'imgproxy source_encoding must be base64, encrypted or plain.',
);

it('throws when base_url is missing', function (string $builder): void {
    config(['imgproxy.base_url' => '']);

    Imgproxy::{$builder}();
})->with(['builder', 'infoBuilder'])->throws(
    InvalidArgumentException::class,
    'imgproxy base_url is not configured.',
);

it('throws when encryption key is missing for encrypted sources', function (string $builder): void {
    config([
        'imgproxy.encryption_key' => null,
        'imgproxy.source_encoding' => 'encrypted',
    ]);

    Imgproxy::{$builder}()->build('https://example.com/private/secret.png');
})->with(['builder', 'infoBuilder'])->throws(
    InvalidArgumentException::class,
    'imgproxy encryption_key is required when source_encoding is encrypted.',
);

it('allows overriding a previously registered IV generator', function (): void {
    config([
        'imgproxy.encryption_key' => '1eb5b0e971ad7f45324c1bb15c947cb207c43152fa5c6c7f35c4f36e0c18e0f1',
        'imgproxy.source_encoding' => 'encrypted',
    ]);

    Imgproxy::buildIvUsing(fn (): string => 'first-iv-123456');
    Imgproxy::buildIvUsing(fn (): string => 'second-iv-654321');

    $url = Imgproxy::builder()->build('https://example.com/locked-again.png');
    $path = parse_url($url, PHP_URL_PATH);
    $segments = explode('/', mb_trim($path, '/'));
    $decoded = base64_decode_url(end($segments));

    expect($decoded)->toStartWith('second-iv-654321');

    Imgproxy::buildIvUsing(null);
});

it('applies presets-only settings independently to each endpoint', function (): void {
    config(['imgproxy.arguments_separator' => ',', 'imgproxy.only_presets' => true]);

    $source = 'https://example.test/photo.jpg';
    $path = 'insecure/thumb,small/'.base64_encode_url($source);

    expect(Imgproxy::builder()->preset(['thumb', 'small'])->build($source))
        ->toBe('https://imgproxy.test/'.$path)
        ->and(Imgproxy::infoBuilder()->size()->build($source))
        ->toBe('https://imgproxy.test/info/insecure/size,1/'.base64_encode_url($source));

    config(['imgproxy.info_only_presets' => true]);

    expect(Imgproxy::infoBuilder()->preset(['thumb', 'small'])->build($source))
        ->toBe('https://imgproxy.test/info/'.$path);
});

it('applies server URL settings to both endpoints', function (): void {
    config([
        'imgproxy.arguments_separator' => ',',
        'imgproxy.info_option_aliases' => ['format' => 'f', 'size' => 's'],
        'imgproxy.option_aliases' => ['format' => 'ext', 'resize' => 'rs'],
        'imgproxy.source_chunk_size' => 8,
        'imgproxy.source_filename' => 'photo.jpg',
        'imgproxy.trusted_signature' => 'trusted',
    ]);

    $source = 'https://example.test/photo.jpg';
    $encoded = implode('/', mb_str_split(base64_encode_url($source), 8)).'/photo.jpg';

    expect(
        Imgproxy::builder()->resize(ResizingType::Fit, width: 100)->format('webp')->build($source),
    )
        ->toBe('https://imgproxy.test/trusted/rs,fit,100/ext,webp/'.$encoded)
        ->and(Imgproxy::infoBuilder()->size()->format()->build($source))
        ->toBe('https://imgproxy.test/info/trusted/s,1/f,1/'.$encoded);
});

it('builds plain source URLs when configured', function (): void {
    config(['imgproxy.source_encoding' => 'plain']);

    $url = Imgproxy::builder()->build('https://example.com/assets/photo.jpg');

    expect($url)->toBe(sprintf(
        'https://imgproxy.test/insecure/plain/%s',
        rawurlencode('https://example.com/assets/photo.jpg'),
    ));
});

it('builds signed image-info URLs using package configuration', function (): void {
    config([
        'imgproxy.key' => '736563726574',
        'imgproxy.salt' => '68656c6c6f',
        'imgproxy.signature_size' => 2,
        'imgproxy.source_encoding' => 'plain',
        'imgproxy.use_short_options' => true,
    ]);

    $source = 'https://example.test/photo.jpg';
    $path = '/s:1/plain/'.rawurlencode($source);
    $signature = base64_encode_url(
        mb_substr(hash_hmac('sha256', 'hello'.$path, 'secret', true), 0, 2, '8bit'),
    );

    expect(Imgproxy::infoBuilder())->toBeInstanceOf(InfoUrlBuilder::class)
        ->and(Imgproxy::infoBuilder()->size()->build($source))
        ->toBe('https://imgproxy.test/info/'.$signature.$path);
});

it('ignores empty optional environment settings', function (): void {
    config([
        'imgproxy.encryption_key' => '',
        'imgproxy.key' => '',
        'imgproxy.salt' => '',
        'imgproxy.trusted_signature' => '',
    ]);

    expect(Imgproxy::builder()->build('https://example.test/photo.jpg'))
        ->toBe(
            'https://imgproxy.test/insecure/'.base64_encode_url('https://example.test/photo.jpg'),
        );
});

it('returns a fresh builder instance on each call', function (): void {
    $first = Imgproxy::builder();
    $second = Imgproxy::builder();

    expect($first)->toBeInstanceOf(UrlBuilder::class)
        ->and($second)->toBeInstanceOf(UrlBuilder::class)
        ->and($first)->not->toBe($second);
});

it('trims the signature to the configured size', function (): void {
    config([
        'imgproxy.key' => '736563726574',
        'imgproxy.salt' => '68656c6c6f',
        'imgproxy.signature_size' => 2,
    ]);

    $url = Imgproxy::builder()
        ->resize(ResizingType::Fit, width: 800, height: 600)
        ->format('jpg')
        ->build('https://example.com/image.jpg');
    $segments = explode('/', mb_trim(parse_url($url, PHP_URL_PATH), '/'));
    $signature = array_shift($segments);
    $path = '/'.implode('/', $segments);
    $expectedSignature = base64_encode_url(mb_substr(hash_hmac(
        'sha256',
        hex2bin('68656c6c6f').$path,
        hex2bin('736563726574'),
        true,
    ), 0, 2, '8bit'));

    expect($path)->toContain('/resize:fit:800:600/format:jpg/')
        ->and($signature)->toBe($expectedSignature)
        ->and($url)->toBe(sprintf('https://imgproxy.test/%s%s', $expectedSignature, $path));
});

it('uses configured encryption key and IV generator', function (): void {
    $iv = '0123456789abcdef';

    config([
        'imgproxy.encryption_key' => '1eb5b0e971ad7f45324c1bb15c947cb207c43152fa5c6c7f35c4f36e0c18e0f1',
        'imgproxy.source_encoding' => 'base64',
    ]);

    Imgproxy::buildIvUsing(fn (): string => $iv);

    $url = Imgproxy::builder()->build(
        'https://example.com/locked.png',
        encoding: SourceEncoding::Encrypted,
    );
    $path = parse_url($url, PHP_URL_PATH);
    $segments = explode('/', mb_trim($path, '/'));
    $decoded = base64_decode_url(end($segments));

    expect($path)->toContain('/enc/')->and($decoded)->toStartWith($iv)
        ->and(
            Imgproxy::infoBuilder()->build(
                'https://example.com/locked.png',
                encoding: SourceEncoding::Encrypted,
            ),
        )
        ->toBe(str_replace('https://imgproxy.test/', 'https://imgproxy.test/info/', $url));

    Imgproxy::buildIvUsing(null);
});

it('uses short option names when configured', function (): void {
    config(['imgproxy.use_short_options' => true]);

    $path = Imgproxy::builder()
        ->format('jpg')
        ->resize(ResizingType::Fit, width: 800, height: 600)
        ->build('https://example.com/image.jpg');

    expect($path)->toContain('/f:jpg/rs:fit:800:600/');
});
