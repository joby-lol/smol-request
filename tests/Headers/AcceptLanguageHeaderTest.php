<?php

/**
 * smolRequest
 * https://github.com/joby-lol/smol-request
 * (c) 2025 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Request\Headers;

use PHPUnit\Framework\TestCase;

class AcceptLanguageHeaderTest extends TestCase
{
    public function test_basic_functionality()
    {
        $header = new AcceptLanguageHeader('en-US', 'fr', 'de-AT', 'en');
        $this->assertEquals('en-us, fr, de-at, en', (string)$header);

        $best = $header->chooseBest('de-DE', 'en-gb', 'fr-FR');
        $this->assertEquals('en-gb', $best);

        $best = $header->chooseBest('es-ES', 'it-IT');
        $this->assertNull($best);

        $best = $header->chooseBest('de-AT', 'en-CA');
        $this->assertEquals('de-at', $best);
    }

    public function test_parse_with_quality_values()
    {
        $header = AcceptLanguageHeader::parse('en-US;q=0.8, fr, de-AT;q=0.9, en;q=0.7');
        $this->assertEquals('fr, de-at, en-us, en', (string)$header);
    }
}
