# Laravel imgproxy

Build imgproxy URLs in Laravel with optional encryption and signing.

## Requirements

- ext-openssl
- Laravel 12+
- PHP 8.4+

## Installation

Install Laravel imgproxy with Composer:

```bash
composer require hosmelq/laravel-imgproxy
```

Set `IMGPROXY_BASE_URL` to the root URL of the imgproxy server. Set
`IMGPROXY_KEY` and `IMGPROXY_SALT` to the hex-encoded key and salt the server
uses to check signatures.

Without a key and salt, URLs are built with `insecure` in place of a signature.
Sources are Base64-encoded by default. Signing and Base64 sources work with any
imgproxy server.

## Build URLs

Start a builder with the `Imgproxy` facade, add processing options, and pass the
source URL to `build()`:

```php
use HosmelQ\Imgproxy\Laravel\Facades\Imgproxy;
use HosmelQ\Imgproxy\ResizingType;

$url = Imgproxy::builder()
    ->resize(ResizingType::Fill, 300, 300)
    ->quality(80)
    ->build('https://example.com/avatar.jpg');
```

The `imgproxy()` helper returns the same builder:

```php
use HosmelQ\Imgproxy\ResizingType;

use function HosmelQ\Imgproxy\Laravel\imgproxy;

$url = imgproxy()
    ->resize(ResizingType::Fit, 1200)
    ->build('https://example.com/header.jpg');
```

Each call starts a fresh builder from the package configuration. Options appear
in the URL in the order they are added. See
[hosmelq/imgproxy](https://github.com/hosmelq/imgproxy-php) for every
processing option.

`format()` sets the output format option without changing the URL's extension.
Pass an extension to `build()` to append one:

```php
$url = Imgproxy::builder()
    ->format('webp')
    ->build('https://example.com/photo.jpg', 'webp');
```

## Build URLs from disks

Call `imgproxy()` on a filesystem disk to use a file's URL as the source:

```php
use HosmelQ\Imgproxy\ResizingType;
use Illuminate\Support\Facades\Storage;

$url = Storage::disk('public')
    ->imgproxy('images/header.jpg')
    ->resize(ResizingType::Fit, 1200)
    ->build('webp');
```

Builder options chain directly from the disk. `build()` takes no source URL, so
its first argument is the output extension.

Use `temporary()` when the source should be a temporary URL from the disk:

```php
$url = Storage::disk('s3')
    ->imgproxy('private/report.png')
    ->temporary(now()->addMinutes(10))
    ->build();
```

`temporary()` also accepts the disk's temporary URL options as its second
argument. The expiration applies to the source URL only. Add `expires()` with a
Unix timestamp when the imgproxy URL should expire too.

## Read image information

Image-info URLs require imgproxy Pro. Start one with `Imgproxy::infoBuilder()`,
or with `imgproxyInfo()` on a disk:

```php
use HosmelQ\Imgproxy\Laravel\Facades\Imgproxy;
use Illuminate\Support\Facades\Storage;

$url = Imgproxy::infoBuilder()
    ->dimensions()
    ->size()
    ->build('https://example.com/photo.jpg');

$url = Storage::disk('public')
    ->imgproxyInfo('photo.jpg')
    ->dimensions()
    ->build();
```

Image-info URLs are signed and encoded the same way as processing URLs. They do
not accept an output extension.

## Encrypt source URLs

Source encryption requires imgproxy Pro. Set `IMGPROXY_SOURCE_ENCODING` to
`encrypted` and `IMGPROXY_ENCRYPTION_KEY` to the hex-encoded key the server
uses. The key must decode to 16, 24, or 32 bytes.

By default, the IV is derived from the source URL and the key, so the same
source always produces the same URL.

Call `Imgproxy::buildIvUsing()` from a service provider to generate the IV with
a custom callback:

```php
use HosmelQ\Imgproxy\Laravel\Facades\Imgproxy;

Imgproxy::buildIvUsing(function (string $sourceUrl): string {
    $hash = hash_hmac('sha256', $sourceUrl, config()->string('app.key'), true);

    return substr($hash, 0, 16);
});
```

The callback receives the source URL, must return exactly 16 bytes, and applies
to processing and image-info URLs. Pass `null` to restore the default. Keep the
IV deterministic: a random IV produces a different URL on every build, which
defeats caching. The example derives the IV from `APP_KEY`, so rotating that key
changes every encrypted URL it generates.

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag=imgproxy-config
```

The [configuration file](config/imgproxy.php) also covers the arguments
separator, signature size, short option names, presets-only mode, option
aliases, source chunking, source filenames, and trusted signatures, each with
its environment variable where one exists. Image-info URLs use
`info_only_presets` and `info_option_aliases` in place of `only_presets` and
`option_aliases`, and share every other setting.

Creating a builder throws an `InvalidArgumentException` when the base URL is
missing, the source encoding is unknown, encrypted sources have no encryption
key, or an option alias names an unknown option.

## Use dependency injection

Inject `ImgproxyManager` when a class should not depend on the facade:

```php
use HosmelQ\Imgproxy\Laravel\ImgproxyManager;
use HosmelQ\Imgproxy\ResizingType;

final class AvatarUrl
{
    public function __construct(private ImgproxyManager $imgproxy) {}

    public function handle(string $source): string
    {
        return $this->imgproxy->builder()
            ->resize(ResizingType::Fill, 300, 300)
            ->build($source);
    }
}
```

## Deploy imgproxy

Deploy a ready-to-run imgproxy server with the Railway template:

[![Deploy on Railway](https://railway.com/button.svg)](https://railway.com/deploy/imgproxy?referralCode=i6jUWN&utm_medium=integration&utm_source=template&utm_campaign=generic)

## Development

Run the test suite with:

```bash
composer test
```

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for a list of changes.

## Contributing

Pull requests are welcome. Please run the test suite before submitting changes.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
