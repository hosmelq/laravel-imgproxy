<?php

declare(strict_types=1);

namespace HosmelQ\Imgproxy\Laravel\Tests;

use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    use WithWorkbench;

    /**
     * {@inheritDoc}
     */
    protected function defineEnvironment($app): void
    {
        config([
            'filesystems.default' => 'public',
            'filesystems.disks.public' => [
                'driver' => 'local',
                'root' => storage_path('app/public'),
                'url' => 'https://example.test',
                'visibility' => 'public',
            ],
            'imgproxy.base_url' => 'https://imgproxy.test',
        ]);
    }
}
