<?php

/**
 * smolRequest
 * https://github.com/joby-lol/smol-request
 * (c) 2025 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Request\Headers;

use PHPUnit\Framework\TestCase;

class RangeHeaderSuffixTest extends TestCase
{
    public function test_basic_functionality(): void
    {
        $suffix = new RangeHeaderSuffix(500);
        $this->assertEquals(500, $suffix->end_bytes);
        $this->assertEquals("-500", (string)$suffix);
    }

    public function test_invalid_suffix_throws_exception(): void
    {
        $this->expectException(HeaderException::class);
        new RangeHeaderSuffix(0);
    }
}
