<?php

/**
 * smolRequest
 * https://github.com/joby-lol/smol-request
 * (c) 2025 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Request\Post;

/**
 * Class for storing post data, including posted values and uploaded files.
 */
class Post
{
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

    public function get(string $key, ?string $default = null): ?string
    {
        return $this->args[$key] ?? $default;
    }

    public function require(string $key): string
    {
        return $this->args[$key] ?? throw new PostException("Missing required POST value: $key");
    }

    public function getInt(string $key, ?int $default = null): ?int
    {
        if (!isset($this->args[$key])) {
            return $default;
        }
        $value = (int) $this->args[$key];
        if ($value != $this->args[$key]) {
            throw new PostException("Invalid POST integer: $key: $value");
        }
        return $value;
    }

    public function requireInt(string $key): int
    {
        $value = $this->getInt($key);
        if (is_null($value)) {
            throw new PostException("Missing required POST integer: $key");
        }
        return $value;
    }

    public function getBool(string $key, ?bool $default = null): ?bool
    {
        if (!isset($this->args[$key])) {
            return $default;
        }
        $value = strtolower($this->args[$key]);
        return match ($value) {
            '1', 'true', 'on', 'yes' => true,
            '0', 'false', 'off', 'no' => false,
            default => throw new PostException("Invalid POST boolean: $key: $value"),
        };
    }

    public function requireBool(string $key): bool
    {
        $value = $this->getBool($key);
        if (is_null($value)) {
            throw new PostException("Missing required POST boolean: $key");
        }
        return $value;
    }

    public function getFloat(string $key, ?float $default = null): ?float
    {
        if (!isset($this->args[$key])) {
            return $default;
        }
        $value = (float) $this->args[$key];
        if ($value != $this->args[$key]) {
            throw new PostException("Invalid POST float: $key: $value");
        }
        return $value;
    }

    public function requireFloat(string $key): float
    {
        $value = $this->getFloat($key);
        if (is_null($value)) {
            throw new PostException("Missing required POST float: $key");
        }
        return $value;
    }

    public function has(string $key): bool
    {
        return isset($this->args[$key]);
    }

    public function file(string $name): ?PostFile
    {
        if (!isset($this->files[$name])) {
            return null;
        }
        if (count($this->files[$name]) > 1) {
            throw new UploadException("Multiple files uploaded for single file field: $name");
        }
        return $this->files[$name][0];
    }

    public function requireFile(string $name): PostFile
    {
        return $this->file($name)
            ?? throw new UploadException("No file uploaded for required field: $name");
    }

    /**
     * @return array<PostFile>
     */
    public function files(string $name): array
    {
        return $this->files[$name] ?? [];
    }

    /**
     * @return array<PostFile>
     */
    public function requireFiles(string $name, int $min = 0, int|float $max = INF): array
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
}
