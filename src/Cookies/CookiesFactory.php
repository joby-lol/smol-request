<?php

/**
 * smolRequest
 * https://github.com/joby-lol/smol-request
 * (c) 2025 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Request\Cookies;

use Stringable;

class CookiesFactory
{
    /**
     * @param array<string,string|Stringable|int|float|bool|null> $cookies
     */
    public function fromArray(array $cookies): Cookies
    {
        return new Cookies($cookies);
    }

    public function fromGlobals(): Cookies
    {
        // @phpstan-ignore-next-line $_COOKIE is always array<string, string>
        return $this->fromArray($_COOKIE);
    }
}
