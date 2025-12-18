<?php

/**
 * smolRequest
 * https://github.com/joby-lol/smol-request
 * (c) 2025 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Request\Headers;

use PHPUnit\Framework\TestCase;

class RangeHeaderTest extends TestCase
{
    public function test_parsing()
    {
        $range = RangeHeader::parse("bytes=-500,500-999,0-499");
        $this->assertCount(3, $range->ranges);
        $this->assertEquals("0-499", (string)$range->ranges[0]);
        $this->assertEquals("500-999", (string)$range->ranges[1]);
        $this->assertEquals("-500", (string)$range->ranges[2]);
        $this->assertEquals("bytes=0-499, 500-999, -500", (string)$range);
    }

    public function test_invalid_unit_throws_exception()
    {
        $this->expectException(HeaderException::class);
        RangeHeader::parse("kilobytes=0-499");
    }

    public function test_invalid_end_byte_in_suffix_range_throws_exception()
    {
        $this->expectException(HeaderException::class);
        RangeHeader::parse("bytes=-foo");
    }

    public function test_invalid_range_format_throws_exception()
    {
        $this->expectException(HeaderException::class);
        RangeHeader::parse("bytes=500");
    }

    public function test_invalid_start_byte_in_range_throws_exception()
    {
        $this->expectException(HeaderException::class);
        RangeHeader::parse("bytes=foo-500");
    }

    public function test_invalid_end_byte_in_range_throws_exception()
    {
        $this->expectException(HeaderException::class);
        RangeHeader::parse("bytes=500-bar");
    }
}
