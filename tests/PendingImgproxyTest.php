<?php

declare(strict_types=1);

use HosmelQ\Imgproxy\Laravel\PendingImgproxy;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Mockery\MockInterface;

it('registers the filesystem macro and keeps pending instances immutable', function (): void {
    $pending = Storage::disk('public')->imgproxy('images/example.jpg');

    $formatted = $pending->format('webp');

    expect($pending)->toBeInstanceOf(PendingImgproxy::class)
        ->and($formatted)->toBeInstanceOf(PendingImgproxy::class)
        ->and($formatted)->not->toBe($pending);
});

it('builds URLs after forwarding builder calls', function (): void {
    $disk = Storage::disk('public');
    $source = $disk->url('images/example.jpg');

    $url = $disk->imgproxy('images/example.jpg')
        ->format('webp')
        ->build();

    expect($url)->toBe(sprintf(
        'https://imgproxy.test/insecure/format:webp/%s.webp',
        base64_encode_url($source)
    ));
});

it('uses the disk temporary URL when configured', function (): void {
    $expiration = now()->addMinutes(5);
    $options = ['ResponseContentType' => 'image/jpeg'];

    $disk = $this->mock(FilesystemAdapter::class, function (MockInterface $mock) use ($expiration, $options): void {
        $mock->shouldReceive('temporaryUrl')
            ->once()
            ->with('photo.png', $expiration, $options)
            ->andReturn('https://example.test/temp-url?signature=abc123');
    });

    $url = PendingImgproxy::for($disk, 'photo.png')
        ->format('jpg')
        ->temporary($expiration, $options)
        ->build();

    expect($url)->toBe(sprintf(
        'https://imgproxy.test/insecure/format:jpg/%s.jpg',
        base64_encode_url('https://example.test/temp-url?signature=abc123')
    ));
});
