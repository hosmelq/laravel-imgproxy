<?php

declare(strict_types=1);

use function Safe\hex2bin;

use HosmelQ\Imgproxy\Laravel\Facades\Imgproxy;
use HosmelQ\Imgproxy\Laravel\ImgproxyManager;
use HosmelQ\Imgproxy\ResizingType;
use HosmelQ\Imgproxy\UrlBuilder;

it('throws when base_url is missing', function (): void {
    config(['imgproxy.base_url' => '']);

    resolve(ImgproxyManager::class)->builder();
})->throws(InvalidArgumentException::class, 'imgproxy base_url is not configured.');

it('throws when encryption key is missing for encrypted sources', function (): void {
    config([
        'imgproxy.encryption_key' => null,
        'imgproxy.source_encoding' => 'encrypted',
    ]);

    Imgproxy::builder()->build('https://example.com/private/secret.png');
})->throws(InvalidArgumentException::class, 'imgproxy encryption_key is required when source_encoding is encrypted.');

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

    $segments = explode('/', trim(parse_url($url, PHP_URL_PATH), '/'));
    $signature = array_shift($segments);
    $path = '/'.implode('/', $segments);
    $expectedSignature = base64_encode_url(substr(hash_hmac(
        'sha256',
        hex2bin('68656c6c6f').$path,
        hex2bin('736563726574'),
        true
    ), 0, 2));

    expect($path)->toContain('/format:jpg/resize:fit:800:600/')
        ->and($signature)->toBe($expectedSignature)
        ->and($url)->toBe(sprintf('https://imgproxy.test/%s%s', $expectedSignature, $path));
});

it('builds plain source URLs when configured', function (): void {
    config(['imgproxy.source_encoding' => 'plain']);

    $url = Imgproxy::builder()->build('https://example.com/assets/photo.jpg');

    expect($url)->toBe(sprintf(
        'https://imgproxy.test/insecure/plain/%s',
        rawurlencode('https://example.com/assets/photo.jpg')
    ));
});

it('uses short option names when configured', function (): void {
    config(['imgproxy.use_short_options' => true]);

    $path = Imgproxy::builder()
        ->format('jpg')
        ->resize(ResizingType::Fit, width: 800, height: 600)
        ->build('https://example.com/image.jpg');

    expect($path)->toContain('/f:jpg/rs:fit:800:600/');
});

it('uses configured encryption key and IV generator', function (): void {
    $iv = '0123456789abcdef';

    config([
        'imgproxy.encryption_key' => '1eb5b0e971ad7f45324c1bb15c947cb207c43152fa5c6c7f35c4f36e0c18e0f1',
        'imgproxy.source_encoding' => 'encrypted',
    ]);

    Imgproxy::buildIvUsing(fn (): string => $iv);

    $url = Imgproxy::builder()->build('https://example.com/locked.png');
    $path = parse_url($url, PHP_URL_PATH);
    $segments = explode('/', trim($path, '/'));
    $decoded = base64_decode_url(end($segments));

    expect($path)->toContain('/enc/')
        ->and($decoded)->toStartWith($iv);

    Imgproxy::buildIvUsing(null);
});

it('allows overriding a previously registered IV generator', function (): void {
    config([
        'imgproxy.encryption_key' => '1eb5b0e971ad7f45324c1bb15c947cb207c43152fa5c6c7f35c4f36e0c18e0f1',
        'imgproxy.source_encoding' => 'encrypted',
    ]);

    Imgproxy::buildIvUsing(fn (): string => 'first-iv-123456');
    Imgproxy::buildIvUsing(fn (): string => 'second-iv-654321');

    $url = Imgproxy::builder()->build('https://example.com/locked-again.png');
    $path = parse_url($url, PHP_URL_PATH);
    $segments = explode('/', trim($path, '/'));
    $decoded = base64_decode_url(end($segments));

    expect($decoded)->toStartWith('second-iv-654321');

    Imgproxy::buildIvUsing(null);
});

it('falls back to base64 encoding when source encoding is unknown', function (): void {
    config(['imgproxy.source_encoding' => 'unknown']);

    $source = 'https://example.com/fallback.jpg';
    $url = Imgproxy::builder()->build($source);

    expect($url)->toContain(base64_encode_url($source))
        ->and($url)->not->toContain('/enc/', '/plain/');
});
