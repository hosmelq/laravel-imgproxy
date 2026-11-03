<?php

declare(strict_types=1);

namespace HosmelQ\Imgproxy\Laravel;

use Illuminate\Filesystem\FilesystemAdapter;
use Override;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ImgproxyServiceProvider extends PackageServiceProvider
{
    /**
     * Configure package.
     */
    #[Override]
    public function configurePackage(Package $package): void
    {
        $package->name('laravel-imgproxy')
            ->hasConfigFile()
            ->hasInstallCommand(function (InstallCommand $command): void {
                $command->askToStarRepoOnGitHub('hosmelq/laravel-imgproxy')->publishConfigFile();
            });
    }

    /**
     * Bootstrap any application services.
     */
    #[Override]
    public function packageBooted(): void
    {
        FilesystemAdapter::macro('imgproxy', function (string $path): PendingImgproxy {
            return PendingImgproxy::for($this, $path);
        });

        FilesystemAdapter::macro('imgproxyInfo', function (string $path): PendingImgproxy {
            return PendingImgproxy::forInfo($this, $path);
        });
    }

    /**
     * Register any application services.
     */
    #[Override]
    public function packageRegistered(): void
    {
        $this->app->singleton(ImgproxyManager::class);
    }
}
