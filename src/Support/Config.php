<?php

declare(strict_types=1);

namespace HosmelQ\Imgproxy\Laravel\Support;

use Illuminate\Support\Facades\Config as ConfigFacade;
use InvalidArgumentException;

class Config
{
    /**
     * Get the option argument separator.
     */
    public static function argumentsSeparator(): string
    {
        return ConfigFacade::string('imgproxy.arguments_separator');
    }

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
        return self::nullableString('imgproxy.encryption_key');
    }

    /**
     * Check whether image-info URLs use only presets.
     */
    public static function infoOnlyPresets(): bool
    {
        return ConfigFacade::boolean('imgproxy.info_only_presets');
    }

    /**
     * Get image-info option aliases.
     *
     * @return array<string, string>
     */
    public static function infoOptionAliases(): array
    {
        return self::aliases('imgproxy.info_option_aliases');
    }

    /**
     * Get the signing key (hex).
     */
    public static function key(): null|string
    {
        return self::nullableString('imgproxy.key');
    }

    /**
     * Check whether processing URLs use only presets.
     */
    public static function onlyPresets(): bool
    {
        return ConfigFacade::boolean('imgproxy.only_presets');
    }

    /**
     * Get processing option names mapped to aliases.
     *
     * @return array<string, string>
     */
    public static function optionAliases(): array
    {
        return self::aliases('imgproxy.option_aliases');
    }

    /**
     * Get the salt (hex).
     */
    public static function salt(): null|string
    {
        return self::nullableString('imgproxy.salt');
    }

    /**
     * Get the signature size.
     */
    public static function signatureSize(): null|int
    {
        return is_null(ConfigFacade::get('imgproxy.signature_size'))
            ? null
            : ConfigFacade::integer('imgproxy.signature_size');
    }

    /**
     * Get the Base64 or encrypted source chunk size in characters.
     */
    public static function sourceChunkSize(): int
    {
        return ConfigFacade::integer('imgproxy.source_chunk_size');
    }

    /**
     * Get the default source encoding value.
     */
    public static function sourceEncoding(): string
    {
        return ConfigFacade::string('imgproxy.source_encoding');
    }

    /**
     * Get the filename appended to encoded sources.
     */
    public static function sourceFilename(): string
    {
        return ConfigFacade::string('imgproxy.source_filename');
    }

    /**
     * Get the trusted signature prefix.
     */
    public static function trustedSignature(): null|string
    {
        return self::nullableString('imgproxy.trusted_signature');
    }

    /**
     * Check if short option names are enabled.
     */
    public static function useShortOptions(): bool
    {
        return ConfigFacade::boolean('imgproxy.use_short_options');
    }

    /**
     * Read an option alias map.
     *
     * @return array<string, string>
     */
    private static function aliases(string $key): array
    {
        $aliases = [];

        foreach (ConfigFacade::array($key) as $name => $alias) {
            if (! is_string($name) || ! is_string($alias)) {
                throw new InvalidArgumentException($key.' must map option names to strings.');
            }

            $aliases[$name] = $alias;
        }

        return $aliases;
    }

    /**
     * Read an optional string without relying on runtime assertions.
     */
    private static function nullableString(string $key): null|string
    {
        if (is_null(ConfigFacade::get($key))) {
            return null;
        }

        $value = ConfigFacade::string($key);

        return $value === '' ? null : $value;
    }
}
