<?php

/**
 * smolRequest
 * https://github.com/joby-lol/smol-request
 * (c) 2025 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Request\Headers;

use PHPUnit\Framework\TestCase;

class GenericHeaderTest extends TestCase
{
    public function test_basic_functionality()
    {
        $header = GenericHeader::parse('some value');
        $this->assertEquals('some value', (string)$header);
        $this->assertEquals('some value', $header->value);
    }
}
