<?php

declare(strict_types=1);

use HosmelQ\Imgproxy\Laravel\PendingImgproxy;
use HosmelQ\Imgproxy\SourceEncoding;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Mockery\MockInterface;

it('rejects output extensions for image-info URLs', function (): void {
    Storage::disk('public')->imgproxyInfo('photo.png')->build(extension: 'jpg');
})->throws(InvalidArgumentException::class, 'Image-info URLs do not support output extensions.');

it('builds image-info URLs from temporary storage sources', function (): void {
    $expiration = now()->addMinutes(5);
    $options = ['ResponseContentType' => 'image/jpeg'];
    $disk = $this->mock(
        FilesystemAdapter::class,
        function (MockInterface $mock) use ($expiration, $options): void {
            $mock->shouldReceive('temporaryUrl')->once()->with('photo.png', $expiration, $options)
                ->andReturn('https://example.test/temporary?signature=abc123');
        },
    );
    $pending = PendingImgproxy::forInfo($disk, 'photo.png');
    $configured = $pending->size()->temporary($expiration, $options);

    expect($configured)->not->toBe($pending)
        ->and($configured->build(encoding: SourceEncoding::Plain))->toBe(
            'https://imgproxy.test/info/insecure/size:1/plain/'
                .rawurlencode('https://example.test/temporary?signature=abc123'),
        );
});

it('builds URLs after forwarding builder calls', function (): void {
    $disk = Storage::disk('public');
    $source = $disk->url('images/example.jpg');
    $url = $disk
        ->imgproxy('images/example.jpg')
        ->format(extension: 'webp')
        ->build(extension: 'webp');

    expect($url)->toBe(sprintf(
        'https://imgproxy.test/insecure/format:webp/%s.webp',
        base64_encode_url($source),
    ));
});

it('registers the filesystem macro and keeps pending instances immutable', function (): void {
    $pending = Storage::disk('public')->imgproxy('images/example.jpg');
    $formatted = $pending->format('webp');

    expect($pending)->toBeInstanceOf(PendingImgproxy::class)
        ->and($formatted)->toBeInstanceOf(PendingImgproxy::class)
        ->and($formatted)->not->toBe($pending);
});

it('uses the disk temporary URL when configured', function (): void {
    $expiration = now()->addMinutes(5);
    $options = ['ResponseContentType' => 'image/jpeg'];
    $disk = $this->mock(
        FilesystemAdapter::class,
        function (MockInterface $mock) use ($expiration, $options): void {
            $mock->shouldReceive('temporaryUrl')
                ->once()
                ->with('photo.png', $expiration, $options)
                ->andReturn('https://example.test/temp-url?signature=abc123');
        },
    );
    $url = PendingImgproxy::for($disk, 'photo.png')
        ->format('jpg')
        ->temporary($expiration, $options)
        ->build(extension: 'jpg');

    expect($url)->toBe(sprintf(
        'https://imgproxy.test/insecure/format:jpg/%s.jpg',
        base64_encode_url('https://example.test/temp-url?signature=abc123'),
    ));
});
