<?php

/**
 * smolRequest
 * https://github.com/joby-lol/smol-request
 * (c) 2025 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Request\Cookies;

use Stringable;

/**
 * Class for storing incoming cookies as an immutable collection. This class limits the types of values that can be stored in the cookie to strings. All other data types must be represented as strings to be stored here. Key names must also be strings. If it is constructed from values that didn't come from the $_COOKIE superglobal, it is the responsibility of the caller to ensure that the data is safe and valid for use as cookies, and exceptions will be thrown if invalid data is provided.
 */
readonly class Cookies
{
    /**
     * @var array<string,string> $cookies the raw string values of all cookies
     */
    public array $cookies;

    /**
     * @param array<string,string|Stringable|int|float|bool|null> $cookies
     */
    public function __construct(array $cookies)
    {
        $built_cookies = [];
        foreach ($cookies as $key => $value) {
            // @phpstan-ignore-next-line we do want to check this at runtime
            if (!is_string($key)) {
                throw new CookieException("Cookie keys must be strings");
            }
            // @phpstan-ignore-next-line we do want to check this at runtime
            if (is_array($value)) {
                throw new CookieException("Invalid cookie value type: array");
            }
            if (is_null($value)) {
                continue;
            }
            if (is_bool($value)) {
                $built_cookies[$key] = $value ? 'true' : 'false';
                continue;
            }
            if ($value instanceof Stringable) {
                $built_cookies[$key] = (string) $value;
                continue;
            }
            if (is_string($value)) {
                $built_cookies[$key] = $value;
                continue;
            }
            if (!is_scalar($value)) { // @phpstan-ignore-line we do want to check this at runtime
                throw new CookieException("Invalid cookie value type: " . gettype($value));
            }
            $built_cookies[$key] = (string) $value;
        }
        ksort($built_cookies);
        $this->cookies = $built_cookies;
    }

    public function get(string $key, ?string $default = null): ?string
    {
        return $this->cookies[$key] ?? $default;
    }

    public function require(string $key): string
    {
        return $this->cookies[$key] ?? throw new CookieException("Missing required cookie string: $key");
    }

    public function getInt(string $key, ?int $default = null): ?int
    {
        if (!isset($this->cookies[$key])) {
            return $default;
        }
        $value = (int) $this->cookies[$key];
        if ($value != $this->cookies[$key]) {
            throw new CookieException("Invalid cookie integer: $key = " . $this->cookies[$key]);
        }
        return $value;
    }

    public function requireInt(string $key): int
    {
        $value = $this->getInt($key);
        if (is_null($value)) {
            throw new CookieException("Missing required cookie integer: $key");
        }
        return $value;
    }

    public function getBool(string $key, ?bool $default = null): ?bool
    {
        if (!isset($this->cookies[$key])) {
            return $default;
        }
        $value = strtolower($this->cookies[$key]);
        return match (strtolower($value)) {
            '1', 'true', 'on', 'yes' => true,
            '0', 'false', 'off', 'no' => false,
            default => throw new CookieException("Invalid cookie boolean: $key = " . $this->cookies[$key]),
        };
    }

    public function requireBool(string $key): bool
    {
        $value = $this->getBool($key);
        if (is_null($value)) {
            throw new CookieException("Missing required cookie boolean: $key");
        }
        return $value;
    }

    public function getFloat(string $key, ?float $default = null): ?float
    {
        if (!isset($this->cookies[$key])) {
            return $default;
        }
        $value = (float) $this->cookies[$key];
        if ($value != $this->cookies[$key]) {
            throw new CookieException("Invalid cookie float: $key = " . $this->cookies[$key]);
        }
        return $value;
    }

    public function requireFloat(string $key): float
    {
        $value = $this->getFloat($key);
        if (is_null($value)) {
            throw new CookieException("Missing required cookie float: $key");
        }
        return $value;
    }

    public function has(string $key): bool
    {
        return isset($this->cookies[$key]);
    }
}
