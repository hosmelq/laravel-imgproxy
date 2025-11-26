<?php

declare(strict_types=1);

use HosmelQ\Imgproxy\Laravel\Facades\Imgproxy;
use HosmelQ\Imgproxy\Laravel\ImgproxyManager;
use HosmelQ\Imgproxy\Laravel\PendingImgproxy;
use HosmelQ\Imgproxy\UrlBuilder;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;

it('registers the facade, macro, and manager singleton', function (): void {
    $first = resolve(ImgproxyManager::class);
    $second = resolve(ImgproxyManager::class);

    expect(Imgproxy::builder())->toBeInstanceOf(UrlBuilder::class)
        ->and(FilesystemAdapter::hasMacro('imgproxy'))->toBeTrue()
        ->and(Storage::disk('public')->imgproxy('images/path.jpg'))->toBeInstanceOf(PendingImgproxy::class)
        ->and($first)->toBe($second);
});
