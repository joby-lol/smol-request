<?php

/**
 * smolRequest
 * https://github.com/joby-lol/smol-request
 * (c) 2025 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Request\Headers;

use PHPUnit\Framework\TestCase;

class HeadersTest extends TestCase
{

    public function test_instantiation_sets_properties_correctly(): void
    {
        $accept = $this->createMock(AcceptHeader::class);
        $range  = $this->createMock(RangeHeader::class);

        $headers = new Headers(
            accept: $accept,
            range: $range,
            if_modified_since: null,
            if_none_match: null,
            generic: [],
        );

        $this->assertSame($accept, $headers->accept);
        $this->assertSame($range, $headers->range);
        $this->assertNull($headers->if_modified_since);
        $this->assertEmpty($headers->generic);
    }

    public function test_has_detects_standard_headers_case_insensitively(): void
    {
        $headers = new Headers(
            accept: $this->createMock(AcceptHeader::class),
            range: null,
            if_modified_since: null,
            if_none_match: null,
            generic: [],
        );

        // Test standard presence
        $this->assertTrue($headers->has('Accept'));
        $this->assertTrue($headers->has('accept'));
        $this->assertTrue($headers->has('ACCEPT'));
        $this->assertFalse($headers->has('range'));
        $this->assertFalse($headers->has('if-modified-since'));
        $this->assertFalse($headers->has('if-none-match'));

        // Test standard absence
        $this->assertFalse($headers->has('Range'));
        $this->assertFalse($headers->has('If-Modified-Since'));
    }

    public function test_get_returns_standard_headers_case_insensitively(): void
    {
        $accept = $this->createMock(AcceptHeader::class);

        $headers = new Headers(
            accept: $accept,
            range: null,
            if_modified_since: null,
            if_none_match: null,
            generic: [],
        );

        $this->assertSame($accept, $headers->get('Accept'));
        $this->assertSame($accept, $headers->get('accept'));
        $this->assertNull($headers->get('Range'));
    }

    public function test_has_detects_generic_headers(): void
    {
        $headers = new Headers(
            accept: null,
            range: null,
            if_modified_since: null,
            if_none_match: null,
            generic: [
                'x-custom-token' => $this->createMock(GenericHeader::class),
            ],
        );
        $this->assertTrue($headers->has('X-Custom-Token'));
        $this->assertTrue($headers->has('x-custom-token'));
        $this->assertFalse($headers->has('X-Non-Existent'));
    }

    public function test_get_returns_generic_headers(): void
    {
        $generic_mock = $this->createMock(GenericHeader::class);

        $headers = new Headers(
            accept: null,
            range: null,
            if_modified_since: null,
            if_none_match: null,
            generic: [
                'content-type' => $generic_mock,
            ],
        );
        $this->assertSame($generic_mock, $headers->get('Content-Type'));
        $this->assertSame($generic_mock, $headers->get('content-type'));
    }

    public function test_get_returns_null_for_missing_generic_header(): void
    {
        $headers = new Headers(
            accept: null,
            range: null,
            if_modified_since: null,
            if_none_match: null,
            generic: [],
        );

        $this->assertNull($headers->get('X-Missing'));
    }

    public function test_standard_properties_take_precedence_over_generic_array(): void
    {
        $typed_accept   = $this->createMock(AcceptHeader::class);
        $generic_accept = $this->createMock(GenericHeader::class);
        $headers        = new Headers(
            accept: $typed_accept,
            range: null,
            if_modified_since: null,
            if_none_match: null,
            generic: [
                'accept' => $generic_accept,
            ],
        );
        $this->assertSame($typed_accept, $headers->get('Accept'));
        $this->assertNotSame($generic_accept, $headers->get('Accept'));
    }

}
