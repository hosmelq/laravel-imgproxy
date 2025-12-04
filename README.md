# Laravel imgproxy

Build imgproxy URLs in Laravel with optional encryption and signing.

## Introduction

Build imgproxy URLs from Laravel with every documented option (free and pro). Grab a builder from the facade for external sources, or call the `imgproxy` disk macro when you want to start from storage paths.

```php
use HosmelQ\Imgproxy\Laravel\Facades\Imgproxy;
use HosmelQ\Imgproxy\ResizingType;

$url = Imgproxy::builder()
    ->format(extension: 'png')
    ->resize(type: ResizingType::Fit, width: 1200, height: 630)
    ->build(sourceUrl: 'https://example.com/image.jpg');
```

## Requirements

- PHP 8.3+
- Laravel 11+
- OpenSSL extension (for source encryption)

## Installation & setup

Install via Composer:

```bash
composer require hosmelq/laravel-imgproxy
```

### Publishing the config file

Optionally publish the config file:

```bash
php artisan vendor:publish --tag="imgproxy-config"
```

<details>
<summary>View the published config file.</summary>

```php
<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Base URL
    |--------------------------------------------------------------------------
    |
    | This URL is used when generating imgproxy links. Set it to the root of
    | your imgproxy instance, including scheme and host.
    |
    */

    'base_url' => env('IMGPROXY_BASE_URL'),

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | Hex-encoded AES key used for encrypted sources. Set this only when the
    | source encoding below is configured to "encrypted".
    |
    */

    'encryption_key' => env('IMGPROXY_ENCRYPTION_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Signing Keys
    |--------------------------------------------------------------------------
    |
    | Hex-encoded key and salt used to sign imgproxy URLs. Provide both or
    | leave both empty. Signature size (bytes) trims the signature length
    | when your server expects a shorter value.
    |
    */

    'key' => env('IMGPROXY_KEY'),

    'salt' => env('IMGPROXY_SALT'),

    'signature_size' => env('IMGPROXY_SIGNATURE_SIZE'),

    /*
    |--------------------------------------------------------------------------
    | Source Encoding
    |--------------------------------------------------------------------------
    |
    | Controls how sources are encoded before signing.
    |
    | Supported encodings: "base64", "encrypted", "plain"
    |
    */

    'source_encoding' => env('IMGPROXY_SOURCE_ENCODING', 'base64'),

    /*
    |--------------------------------------------------------------------------
    | Option Names
    |--------------------------------------------------------------------------
    |
    | Use imgproxy's short option names instead of the long names.
    |
    */

    'use_short_options' => env('IMGPROXY_SHORT_OPTIONS', false),

];
```
</details>

## Basic usage

### Getting started

Use the builder from the facade. Chain options if needed, then build the URL.

```php
use HosmelQ\Imgproxy\Laravel\Facades\Imgproxy;
use HosmelQ\Imgproxy\ResizingType;
use HosmelQ\Imgproxy\Support\Gravity;

$url = Imgproxy::builder()
    ->format(extension: 'png')
    ->gravity(gravity: Gravity::smart())
    ->quality(quality: 80)
    ->resize(type: ResizingType::Fit, width: 1200, height: 630)
    ->build(sourceUrl: 'https://example.com/assets/product.jpg');
```

### Using the filesystem macro

Call the `imgproxy` macro on any filesystem disk path to build from storage.

```php
use Illuminate\Support\Facades\Storage;

$url = Storage::disk('public')
    ->imgproxy(path: 'images/headers/welcome.jpg')
    ->build();
```

The `imgproxy` macro starts from the disk path, applies your global imgproxy settings, and returns a ready-to-use URL.

### Generating temporary URLs

Wrap your storage temporary URL before imgproxy processes it. Pass expiration and any disk-specific options. This uses the filesystem macro.

```php
use Illuminate\Support\Facades\Storage;

$url = Storage::disk('s3')
    ->imgproxy(path: 'private/reports/weekly.png')
    ->temporary(expiration: now()->addMinutes(10))
    ->build();
```

The disk's temporary URL becomes the source, so the imgproxy link expires in 10 minutes alongside it.

## Advanced usage

### Custom IV generation

Provide your own IV generator for encrypted sources when you need a custom strategy (imgproxy pro).

```php
use HosmelQ\Imgproxy\Laravel\Facades\Imgproxy;

Imgproxy::buildIvUsing(fn (): string => random_bytes(16));
```

Use this when your security policy requires a specific IV pattern. The callback runs on every encrypted source build.

For more builder options and patterns, see the base package: [hosmelq/imgproxy](https://github.com/hosmelq/imgproxy-php).

## Testing

```bash
composer test
```

## Deployments

Want a ready-to-run imgproxy instance? Use the Railway template:

[![Deploy on Railway](https://railway.com/button.svg)](https://railway.com/deploy/imgproxy?referralCode=i6jUWN&utm_medium=integration&utm_source=template&utm_campaign=generic)

## Changelog

Please see [CHANGELOG.md](CHANGELOG.md) for recent changes.

## Credits

- [Hosmel Quintana](https://github.com/hosmelq)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [LICENSE.md](LICENSE.md) for more information.
