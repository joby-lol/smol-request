<?php

/**
 * smolRequest
 * https://github.com/joby-lol/smol-request
 * (c) 2025 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Request\Headers;

use Stringable;

/**
 * Value indicating a suffix range, e.g. "-500" meaning "the last 500 bytes".
 */
readonly class RangeHeaderSuffix implements Stringable
{
    public function __construct(
        public int $end_bytes,
    ) {
        if ($end_bytes <= 0) {
            throw new HeaderException("Invalid Range suffix, byte count must be positive");
        }
    }

    public function __toString(): string
    {
        return "-{$this->end_bytes}";
    }
}
