<?php

declare(strict_types=1);

namespace HosmelQ\Imgproxy\Laravel;

use Closure;
use HosmelQ\Imgproxy\Imgproxy as CoreImgproxy;
use HosmelQ\Imgproxy\InfoOptionName;
use HosmelQ\Imgproxy\InfoUrlBuilder;
use HosmelQ\Imgproxy\Laravel\Support\Config;
use HosmelQ\Imgproxy\OptionName;
use HosmelQ\Imgproxy\UrlBuilder;
use InvalidArgumentException;

final class ImgproxyManager
{
    /**
     * IV generator used for encrypted sources.
     */
    private null|Closure $ivGeneratorCallback = null;

    /**
     * Create a fresh URL builder using package configuration.
     */
    public function builder(): UrlBuilder
    {
        $builder = CoreImgproxy::create(
            baseUrl: $this->baseUrl(),
            key: Config::key(),
            salt: Config::salt(),
            signatureSize: Config::signatureSize(),
            useShortOptions: Config::useShortOptions(),
        );

        return $this->configure($builder);
    }

    /**
     * Register the IV generator for encrypted sources, or null for the default.
     *
     * The callback receives the source URL and must return exactly 16 bytes.
     */
    public function buildIvUsing(null|callable $callback): void
    {
        $this->ivGeneratorCallback = is_null($callback) ? null : $callback(...);
    }

    /**
     * Create a fresh image-info URL builder (imgproxy Pro).
     */
    public function infoBuilder(): InfoUrlBuilder
    {
        $builder = CoreImgproxy::createInfo(
            baseUrl: $this->baseUrl(),
            key: Config::key(),
            salt: Config::salt(),
            signatureSize: Config::signatureSize(),
            useShortOptions: Config::useShortOptions(),
        );

        return $this->configure($builder);
    }

    /**
     * Apply a configured source encoding strategy.
     *
     * @return ($builder is InfoUrlBuilder ? InfoUrlBuilder : UrlBuilder)
     */
    private function applySourceEncoding(
        InfoUrlBuilder|UrlBuilder $builder,
    ): InfoUrlBuilder|UrlBuilder {
        $key = Config::encryptionKey();

        if (! is_null($key)) {
            $builder = $builder->withEncryptionKey($key, $this->ivGeneratorCallback);
        }

        return match (mb_strtolower(Config::sourceEncoding())) {
            'base64' => $builder->useBase64Source(),
            'encrypted' => is_null($key)
                ? throw new InvalidArgumentException(
                    'imgproxy encryption_key is required when source_encoding is encrypted.',
                )
                : $builder->useEncryptedSource(),
            'plain' => $builder->usePlainSource(),
            default => throw new InvalidArgumentException(
                'imgproxy source_encoding must be base64, encrypted or plain.',
            ),
        };
    }

    /**
     * Get the required imgproxy base URL.
     */
    private function baseUrl(): string
    {
        $baseUrl = Config::baseUrl();

        if ($baseUrl === '') {
            throw new InvalidArgumentException('imgproxy base_url is not configured.');
        }

        return $baseUrl;
    }

    /**
     * Apply server URL settings to either endpoint.
     *
     * @return ($builder is InfoUrlBuilder ? InfoUrlBuilder : UrlBuilder)
     */
    private function configure(InfoUrlBuilder|UrlBuilder $builder): InfoUrlBuilder|UrlBuilder
    {
        $builder = $this->applySourceEncoding($builder)
            ->argumentsSeparator(Config::argumentsSeparator())
            ->onlyPresets(
                $builder instanceof InfoUrlBuilder
                    ? Config::infoOnlyPresets()
                    : Config::onlyPresets(),
            )
            ->sourceChunkSize(Config::sourceChunkSize())
            ->sourceFilename(Config::sourceFilename())
            ->trustedSignature(Config::trustedSignature());
        $aliases = $builder instanceof InfoUrlBuilder
            ? Config::infoOptionAliases()
            : Config::optionAliases();

        foreach ($aliases as $name => $alias) {
            $option = $builder instanceof InfoUrlBuilder
                ? InfoOptionName::tryFrom($name)
                : OptionName::tryFrom($name);

            if (is_null($option)) {
                throw new InvalidArgumentException(
                    sprintf('Unknown imgproxy option alias [%s].', $name),
                );
            }

            $builder = $builder->optionAlias($option, $alias);
        }

        return $builder;
    }
}
