<?php

/**
 * smolRequest
 * https://github.com/joby-lol/smol-request
 * (c) 2025 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Request\Headers;

/**
 * A generic header, used to handle any input headers that are not explicitly understood and parsed by some more complex implementation. Stores its value as a simple string.
 */
readonly class GenericHeader implements HeaderInterface
{
    public function __construct(
        public string $value,
    ) {
    }

    public static function parse(string $value): self
    {
        return new GenericHeader($value);
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
