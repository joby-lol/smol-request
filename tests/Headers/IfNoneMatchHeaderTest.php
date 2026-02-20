<?php

/**
 * smolRequest
 * https://github.com/joby-lol/smol-request
 * (c) 2025 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Request\Headers;

use PHPUnit\Framework\TestCase;

class IfNoneMatchHeaderTest extends TestCase
{
    public function test_basic_functionality()
    {
        $header = IfNoneMatchHeader::parse('"etag1", W/"etag2", "etag3"');
        $this->assertEquals(['etag1', 'etag2', 'etag3'], $header->etags);
        $this->assertTrue($header->noneMatch('etag4', 'etag5'));
        $this->assertFalse($header->noneMatch('etag2', 'etag5'));
        $this->assertEquals('"etag1", "etag2", "etag3"', (string)$header);
    }

    public function test_wildcard_matches_any_etag(): void
    {
        $header = IfNoneMatchHeader::parse('*');
        $this->assertFalse($header->noneMatch('anything'));
        $this->assertFalse($header->noneMatch('etag1'));
        $this->assertFalse($header->noneMatch(''));
    }

    public function test_wildcard_string_representation(): void
    {
        $header = IfNoneMatchHeader::parse('*');
        $this->assertEquals('*', (string) $header);
    }
}
