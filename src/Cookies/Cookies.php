<?php

/**
 * smolRequest
 * https://github.com/joby-lol/smol-request
 * (c) 2025 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Request\Cookies;

use Joby\Smol\Cast\CastingGettersTrait;
use Stringable;

/**
 * Class for storing incoming cookies as an immutable collection. This class limits the types of values that can be stored in the cookie to strings. All other data types must be represented as strings to be stored here. Key names must also be strings. If it is constructed from values that didn't come from the $_COOKIE superglobal, it is the responsibility of the caller to ensure that the data is safe and valid for use as cookies, and exceptions will be thrown if invalid data is provided.
 */
readonly class Cookies
{

    use CastingGettersTrait;

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

    public function has(string $key): bool
    {
        return isset($this->cookies[$key]);
    }

    /**
     * @inheritDoc
     */
    protected function createCastException(string $type, string $name, \Throwable $previous): \Throwable
    {
        return new CookieException("Error casting cookie '$name' to type $type: " . $previous->getMessage(), 0, $previous);
    }

    /**
     * @inheritDoc
     */
    protected function createRequiredException(string $type, string $name): \Throwable
    {
        return new CookieException("Missing required cookie $type: $name");
    }

    /**
     * @inheritDoc
     */
    protected function getCastableValue(string $key): mixed
    {
        return $this->get($key);
    }

}
