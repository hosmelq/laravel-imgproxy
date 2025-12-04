<?php

declare(strict_types=1);

namespace HosmelQ\Imgproxy\Laravel;

use HosmelQ\Imgproxy\UrlBuilder;

/**
 * Get a fresh imgproxy URL builder using the configured manager.
 */
function imgproxy(): UrlBuilder
{
    return resolve(ImgproxyManager::class)->builder();
}
