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
 * Value indicating a range of bytes, e.g. "500-999" meaning "from byte 500 to byte 999 inclusive" or "500-" meaning "from byte 500 to the end".
 */
readonly class RangeHeaderRange implements Stringable
{
    public function __construct(
        public int $start_byte,
        public ?int $end_byte,
    ) {
        if ($start_byte < 0) {
            throw new HeaderException("Invalid Range start byte, must be non-negative");
        }
        if (!is_null($end_byte) && $end_byte < $start_byte) {
            throw new HeaderException("Invalid Range end byte, must be greater than or equal to start byte");
        }
    }

    public function __toString(): string
    {
        if (is_null($this->end_byte)) {
            return "{$this->start_byte}-";
        }
        return "{$this->start_byte}-{$this->end_byte}";
    }
}
