<?php

declare(strict_types=1);

namespace HosmelQ\Imgproxy\Laravel\Facades;

use HosmelQ\Imgproxy\Laravel\ImgproxyManager;
use HosmelQ\Imgproxy\UrlBuilder;
use Illuminate\Support\Facades\Facade;

/**
 * @method static UrlBuilder builder()
 * @method static void buildIvUsing(null|callable $callback)
 *
 * @see ImgproxyManager
 */
class Imgproxy extends Facade
{
    /**
     * {@inheritDoc}
     */
    protected static function getFacadeAccessor(): string
    {
        return ImgproxyManager::class;
    }
}
