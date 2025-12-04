<?php

declare(strict_types=1);

namespace HosmelQ\Imgproxy\Laravel;

use BadMethodCallException;
use DateTimeInterface;
use HosmelQ\Imgproxy\UrlBuilder;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Traits\ForwardsCalls;

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
     * Whether to generate a temporary URL from the disk.
     */
    private bool $useTemporary = false;

    /**
     * Create a new pending imgproxy instance.
     */
    private function __construct(
        private readonly FilesystemAdapter $disk,
        private readonly string $path,
        private UrlBuilder $builder
    ) {
    }

    /**
     * Create a new pending instance for the given disk and path.
     */
    public static function for(FilesystemAdapter $disk, string $path): self
    {
        return new self($disk, $path, resolve(ImgproxyManager::class)->builder());
    }

    /**
     * Build the final imgproxy URL for the disk path.
     */
    public function build(): string
    {
        if ($this->useTemporary && $this->temporaryExpiration instanceof DateTimeInterface) {
            return $this->builder->build(
                $this->disk->temporaryUrl(
                    $this->path,
                    $this->temporaryExpiration,
                    $this->temporaryOptions
                )
            );
        }

        return $this->builder->build($this->disk->url($this->path));
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
        $clone->useTemporary = true;

        return $clone;
    }

    /**
     * Clone with a new builder instance.
     */
    private function withBuilder(UrlBuilder $builder): self
    {
        $clone = clone $this;

        $clone->builder = $builder;

        return $clone;
    }

    /**
     * Proxy builder methods for fluent chaining.
     *
     * @param array<int, mixed> $arguments
     */
    public function __call(string $name, array $arguments): self
    {
        $builder = $this->forwardCallTo($this->builder, $name, $arguments);

        if (! $builder instanceof UrlBuilder) {
            throw new BadMethodCallException(sprintf(
                'Method [%s] did not return an imgproxy builder instance.',
                $name
            ));
        }

        return $this->withBuilder($builder);
    }
}
