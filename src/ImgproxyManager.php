<?php

declare(strict_types=1);

namespace HosmelQ\Imgproxy\Laravel;

use Closure;
use HosmelQ\Imgproxy\Imgproxy as CoreImgproxy;
use HosmelQ\Imgproxy\Laravel\Support\Config;
use HosmelQ\Imgproxy\UrlBuilder;
use InvalidArgumentException;

final class ImgproxyManager
{
    /**
     * IV generator used for encrypted sources.
     */
    private static null|Closure $ivGeneratorCallback = null;

    /**
     * Create a fresh URL builder using package configuration.
     */
    public function builder(): UrlBuilder
    {
        $baseUrl = Config::baseUrl();

        if ($baseUrl === '') {
            throw new InvalidArgumentException('imgproxy base_url is not configured.');
        }

        $builder = CoreImgproxy::create(
            baseUrl: $baseUrl,
            key: Config::key(),
            salt: Config::salt(),
            signatureSize: Config::signatureSize(),
            useShortOptions: Config::useShortOptions(),
        );

        return $this->applySourceEncoding($builder);
    }

    /**
     * Register a global IV generator used when encryption is enabled.
     */
    public function buildIvUsing(null|callable $callback): void
    {
        self::$ivGeneratorCallback = is_null($callback) ? null : $callback(...);
    }

    /**
     * Enable encrypted sources with the configured key.
     */
    private function applyEncryption(UrlBuilder $builder): UrlBuilder
    {
        $key = Config::encryptionKey();

        if (is_null($key)) {
            throw new InvalidArgumentException('imgproxy encryption_key is required when source_encoding is encrypted.');
        }

        return $builder->useEncryptedSource()
            ->withEncryptionKey($key, self::$ivGeneratorCallback);
    }

    /**
     * Apply a configured source encoding strategy.
     */
    private function applySourceEncoding(UrlBuilder $builder): UrlBuilder
    {
        $encoding = strtolower(Config::sourceEncoding());

        return match ($encoding) {
            'base64' => $builder->useBase64Source(),
            'encrypted' => $this->applyEncryption($builder),
            'plain' => $builder->usePlainSource(),
            default => $builder->useBase64Source(),
        };
    }
}
