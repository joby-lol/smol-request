<?php

/**
 * smolRequest
 * https://github.com/joby-lol/smol-request
 * (c) 2025 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Request\Headers;

use PHPUnit\Framework\TestCase;

class AcceptHeaderMediaTypeTest extends TestCase
{
    public function test_basic_functionality()
    {
        $type = AcceptHeaderMediaType::parse('text/html;;=;foo; charset=UTF-8');
        $this->assertInstanceOf(AcceptHeaderMediaType::class, $type);
        $this->assertEquals('text', $type->type);
        $this->assertEquals('html', $type->subtype);
        $this->assertEquals(['charset' => 'UTF-8', 'foo' => null], $type->parameters);
        $this->assertEquals('text/html; charset=UTF-8; foo', (string)$type);
    }

    public function test_invalid_inputs()
    {
        $this->assertNull(AcceptHeaderMediaType::parse('invalidtype'));
        $this->assertNull(AcceptHeaderMediaType::parse('text/'));
        $this->assertNull(AcceptHeaderMediaType::parse('/html'));
        $this->assertNull(AcceptHeaderMediaType::parse(''));
    }

    public function test_empty_type_subtype_throws_exception()
    {
        $this->expectException(\InvalidArgumentException::class);
        new AcceptHeaderMediaType('', 'html');
    }

    public function test_empty_subtype_throws_exception()
    {
        $this->expectException(\InvalidArgumentException::class);
        new AcceptHeaderMediaType('text', '');
    }
}
