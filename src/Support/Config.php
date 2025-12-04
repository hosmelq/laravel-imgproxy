<?php

declare(strict_types=1);

namespace HosmelQ\Imgproxy\Laravel\Support;

use Illuminate\Support\Facades\Config as ConfigFacade;

class Config
{
    /**
     * Get the imgproxy base URL.
     */
    public static function baseUrl(): string
    {
        return ConfigFacade::string('imgproxy.base_url');
    }

    /**
     * Get the encryption key (hex).
     */
    public static function encryptionKey(): null|string
    {
        $value = ConfigFacade::get('imgproxy.encryption_key');

        assert(
            is_null($value) || is_string($value),
            sprintf(
                'Configuration value for key [%s] must be a string or null, %s given.',
                'imgproxy.encryption_key',
                gettype($value)
            )
        );

        return $value;
    }

    /**
     * Get the signing key (hex).
     */
    public static function key(): null|string
    {
        $value = ConfigFacade::get('imgproxy.key');

        assert(
            is_null($value) || is_string($value),
            sprintf(
                'Configuration value for key [%s] must be a string or null, %s given.',
                'imgproxy.key',
                gettype($value)
            )
        );

        return $value;
    }

    /**
     * Get the salt (hex).
     */
    public static function salt(): null|string
    {
        $value = ConfigFacade::get('imgproxy.salt');

        assert(
            is_null($value) || is_string($value),
            sprintf(
                'Configuration value for key [%s] must be a string or null, %s given.',
                'imgproxy.salt',
                gettype($value)
            )
        );

        return $value;
    }

    /**
     * Get the signature size.
     */
    public static function signatureSize(): null|int
    {
        $value = ConfigFacade::get('imgproxy.signature_size');

        assert(
            is_null($value) || is_int($value),
            sprintf(
                'Configuration value for key [%s] must be an integer or null, %s given.',
                'imgproxy.signature_size',
                gettype($value)
            )
        );

        return $value;
    }

    /**
     * Get the default source encoding value.
     */
    public static function sourceEncoding(): string
    {
        return ConfigFacade::string('imgproxy.source_encoding');
    }

    /**
     * Check if short option names are enabled.
     */
    public static function useShortOptions(): bool
    {
        return ConfigFacade::boolean('imgproxy.use_short_options');
    }
}
