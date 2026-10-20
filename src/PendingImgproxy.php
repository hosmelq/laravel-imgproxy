<?php

declare(strict_types=1);

namespace HosmelQ\Imgproxy\Laravel;

use BadMethodCallException;
use DateTimeInterface;
use HosmelQ\Imgproxy\InfoUrlBuilder;
use HosmelQ\Imgproxy\SourceEncoding;
use HosmelQ\Imgproxy\UrlBuilder;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Traits\ForwardsCalls;
use InvalidArgumentException;

final class PendingImgproxy
{
    use ForwardsCalls;

    /**
     * Temporary URL expiration.
     */
    private null|DateTimeInterface $temporaryExpiration = null;

    /**
     * Additional temporary URL options.
     *
     * @var array<string, mixed>
     */
    private array $temporaryOptions = [];

    /**
     * Create a new pending imgproxy instance.
     */
    private function __construct(
        private readonly FilesystemAdapter $disk,
        private readonly string $path,
        private InfoUrlBuilder|UrlBuilder $builder,
    ) {}

    /**
     * Create a new pending instance for the given disk and path.
     */
    public static function for(FilesystemAdapter $disk, string $path): self
    {
        return new self($disk, $path, resolve(ImgproxyManager::class)->builder());
    }

    /**
     * Create a pending image-info URL for the given disk and path.
     */
    public static function forInfo(FilesystemAdapter $disk, string $path): self
    {
        return new self($disk, $path, resolve(ImgproxyManager::class)->infoBuilder());
    }

    /**
     * Build the final imgproxy URL for the disk path.
     */
    public function build(
        null|string $extension = null,
        null|SourceEncoding $encoding = null,
    ): string {
        if ($this->builder instanceof InfoUrlBuilder && ! is_null($extension)) {
            throw new InvalidArgumentException('Image-info URLs do not support output extensions.');
        }

        $source = $this->temporaryExpiration instanceof DateTimeInterface
            ? $this->disk->temporaryUrl(
                $this->path,
                $this->temporaryExpiration,
                $this->temporaryOptions,
            )
            : $this->disk->url($this->path);

        return $this->builder instanceof InfoUrlBuilder
            ? $this->builder->build($source, $encoding)
            : $this->builder->build($source, $extension, $encoding);
    }

    /**
     * Use a temporary URL from the disk.
     *
     * @param array<string, mixed> $options
     */
    public function temporary(DateTimeInterface $expiration, array $options = []): self
    {
        $clone = clone $this;

        $clone->temporaryExpiration = $expiration;
        $clone->temporaryOptions = $options;

        return $clone;
    }

    /**
     * Clone with a new builder instance.
     */
    private function withBuilder(InfoUrlBuilder|UrlBuilder $builder): self
    {
        $clone = clone $this;

        $clone->builder = $builder;

        return $clone;
    }

    /**
     * Proxy builder methods for fluent chaining.
     *
     * @param array<array-key, mixed> $arguments
     */
    public function __call(string $name, array $arguments): self
    {
        $builder = $this->forwardCallTo($this->builder, $name, $arguments);

        if (! $builder instanceof UrlBuilder && ! $builder instanceof InfoUrlBuilder) {
            throw new BadMethodCallException(sprintf(
                'Method [%s] did not return an imgproxy builder instance.',
                $name,
            ));
        }

        return $this->withBuilder($builder);
    }
}
