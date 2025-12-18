<?php

/**
 * smolRequest
 * https://github.com/joby-lol/smol-request
 * (c) 2025 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Request\Headers;

use PHPUnit\Framework\TestCase;

class RangeHeaderRangeTest extends TestCase
{
    public function test_basic_functionality(): void
    {
        $suffix = new RangeHeaderRange(100, 500);
        $this->assertEquals(100, $suffix->start_byte);
        $this->assertEquals(500, $suffix->end_byte);
        $this->assertEquals("100-500", (string)$suffix);
        $suffix = new RangeHeaderRange(0, 1500);
        $this->assertEquals("0-1500", (string)$suffix);
        $suffix = new RangeHeaderRange(200, null);
        $this->assertEquals("200-", (string)$suffix);
    }

    public function test_invalid_start_byte_throws_exception(): void
    {
        $this->expectException(HeaderException::class);
        new RangeHeaderRange(-1, 500);
    }

    public function test_invalid_end_byte_throws_exception(): void
    {
        $this->expectException(HeaderException::class);
        new RangeHeaderRange(500, 400);
    }
}
