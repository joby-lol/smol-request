<?php

/**
 * smolRequest
 * https://github.com/joby-lol/smol-request
 * (c) 2025 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Request\Headers;

use PHPUnit\Framework\TestCase;

class IfModifiedSinceHeaderTest extends TestCase
{
    public function test_basic_functionality()
    {
        $header = IfModifiedSinceHeader::parse('Wed, 21 Oct 2015 07:28:00 GMT');
        $this->assertInstanceOf(IfModifiedSinceHeader::class, $header);
        $this->assertEquals('Wed, 21 Oct 2015 07:28:00 GMT', (string)$header);

        $this->assertTrue($header->modifiedSince('Thu, 22 Oct 2015 07:28:00 GMT'));
        $this->assertFalse($header->modifiedSince('Tue, 20 Oct 2015 07:28:00 GMT'));

        $this->assertTrue($header->modifiedSince(strtotime('Thu, 22 Oct 2015 07:28:00 GMT')));
        $this->assertFalse($header->modifiedSince(strtotime('Tue, 20 Oct 2015 07:28:00 GMT')));

        $date = new \DateTimeImmutable('Thu, 22 Oct 2015 07:28:00 GMT');
        $this->assertTrue($header->modifiedSince($date));
        $date = new \DateTimeImmutable('Tue, 20 Oct 2015 07:28:00 GMT');
        $this->assertFalse($header->modifiedSince($date));
    }

    public function test_invalid_date_parsing()
    {
        $header = IfModifiedSinceHeader::parse('invalid-date-string');
        $this->assertNull($header);
    }

    public function test_invalid_date_comparison()
    {
        $header = IfModifiedSinceHeader::parse('Wed, 21 Oct 2015 07:28:00 GMT');
        $this->expectException(\InvalidArgumentException::class);
        $header->modifiedSince('invalid-date-string');
    }
}
