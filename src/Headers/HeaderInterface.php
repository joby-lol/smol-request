<?php

/**
 * smolRequest
 * https://github.com/joby-lol/smol-request
 * (c) 2025 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Request\Headers;

use Stringable;

interface HeaderInterface extends Stringable
{
    /**
     * All request headers must be parseable from a string value. Should return null if the value is invalid or not understood by the implementation, so that other implementations may be tried, and ultimately a generic header used as a fallback.
     */
    public static function parse(string $value): ?self;
}
