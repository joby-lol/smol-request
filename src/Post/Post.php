<?php

/**
 * smolRequest
 * https://github.com/joby-lol/smol-request
 * (c) 2025 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Request\Post;

use Joby\Smol\Cast\CastingGettersTrait;
use Stringable;

/**
 * Class for storing post data, including posted values and uploaded files.
 */
class Post
{

    use CastingGettersTrait;

    protected string $castingRequiredExceptionClass = PostException::class;

    protected string $castingErrorExceptionClass = PostException::class;

    /** @var array<string,string> $args */
    public array $args;
    /** @var array<string,PostFile[]> $files */
    public array $files;

    /**
     * @param array<string,string>         $values
     * @param array<string,PostFile[]> $files
     */
    public function __construct(
        array $values,
        array $files,
    ) {
        ksort($values);
        ksort($files);
        $this->args = $values;
        $this->files = $files;
    }

    public function get(string|Stringable $key, ?string $default = null): ?string
    {
        return $this->args[(string) $key] ?? $default;
    }

    public function require(string|Stringable $key): string
    {
        return $this->args[(string) $key] ?? throw new PostException("Missing required POST value: $key");
    }

    public function has(string|Stringable $key): bool
    {
        return isset($this->args[(string) $key]);
    }

    public function file(string|Stringable $name): ?PostFile
    {
        if (!isset($this->files[(string) $name])) {
            return null;
        }
        if (count($this->files[(string) $name]) > 1) {
            throw new UploadException("Multiple files uploaded for single file field: $name");
        }
        return $this->files[(string) $name][0];
    }

    public function requireFile(string|Stringable $name): PostFile
    {
        return $this->file($name)
            ?? throw new UploadException("No file uploaded for required field: $name");
    }

    /**
     * @return array<PostFile>
     */
    public function files(string|Stringable $name): array
    {
        return $this->files[(string) $name] ?? [];
    }

    /**
     * @return array<PostFile>
     */
    public function requireFiles(string|Stringable $name, int $min = 0, int|float $max = INF): array
    {
        $files = $this->files($name);
        if (count($files) < $min) {
            throw new UploadException("At least $min uploads required for field $name");
        }
        if (count($files) > $max) {
            throw new UploadException("No more than $max uploads allowed for field $name");
        }
        return $files;
    }

    /**
     * @inheritDoc
     */
    protected function getCastableValue(string $key): mixed
    {
        return $this->get($key);
    }

    /**
     * @inheritDoc
     */
    protected function createCastException(string $type, string $name, \Throwable $previous): \Throwable
    {
        return new PostException("Invalid POST $type: $name", previous: $previous);
    }

    /**
     * @inheritDoc
     */
    protected function createRequiredException(string $type, string $name): \Throwable
    {
        return new PostException("Missing required POST $type: $name");
    }
}
