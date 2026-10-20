<?php

declare(strict_types=1);

use HosmelQ\PhpCodingStandard\CodingStandard;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
    ->withCache(__DIR__.'/.cache/ecs')
    ->withPaths([__DIR__.'/config', __DIR__.'/src', __DIR__.'/tests'])
    ->withSets([CodingStandard::ECS_LARAVEL_PACKAGE]);
