<?php

/**
 * smolRequest
 * https://github.com/joby-lol/smol-request
 * (c) 2025 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Request\Headers;

use PHPUnit\Framework\TestCase;

class AcceptHeaderTest extends TestCase
{

    /**
     * To test the protected static method valueSort, we create a wrapper
     * that exposes it publicly.
     */
    public function callValueSort(string $a, string $b): int
    {
        $class =

            new readonly class extends AcceptHeader {

            // Bypass constructor logic for this helper
            public static function publicValueSort(string $a, string $b): int
            {
                return parent::valueSort($a, $b);
            }

            };

        return $class::publicValueSort($a, $b);
    }

    public function test_sorts_wildcards_last(): void
    {
        // "text/html" (specific) should beat "text/*" (subtype wildcard)
        $result = $this->callValueSort('text/html', 'text/*');
        $this->toBeLessThan(0, $result, 'Specific type should come before subtype wildcard');

        // "text/*" (subtype wildcard) should beat "*/*" (global wildcard)
        $result = $this->callValueSort('text/*', '*/*');
        $this->toBeLessThan(0, $result, 'Subtype wildcard should come before global wildcard');

        // Inverse check
        $result = $this->callValueSort('*/*', 'text/html');
        $this->toBeGreaterThan(0, $result);
    }

    public function test_sorts_by_parameter_count_specificity(): void
    {
        // "text/html;level=1" has more parameters than "text/html", so it is more specific
        $a = 'text/html;level=1';
        $b = 'text/html';

        // Expect B to be greater (sorted after) A
        // The logic is: count(a) vs count(b). 
        $result = $this->callValueSort($a, $b);
        $this->toBeLessThan(0, $result, 'More parameters should be sorted first (considered more specific)');
    }

    public function test_sorts_equal_specificity_alphabetically(): void
    {
        // Both are specific, no parameters. Should fall back to string comparison.
        $a = 'application/json';
        $b = 'application/xml';

        $result = $this->callValueSort($a, $b);

        // 'j' comes before 'x', so strcmp returns < 0
        $this->toBeLessThan(0, $result);
    }

    public function test_sort_handles_complex_wildcard_scenario(): void
    {
        // Scenario: */* vs text/*
        // A starts with *, B does not. 
        // Code: if ($a[0][0] === '*' && $b[0][0] !== '*') return 1;
        $result = $this->callValueSort('*/*', 'text/*');
        $this->toBeGreaterThan(0, $result, '*/* should be sorted after text/*');
    }

    public function test_construct_filters_and_uniques_items(): void
    {
        $header = new AcceptHeader(
            'text/html',
            'text/html', // Duplicate
            'application/json',
        );
        // Accessing the public $types property
        $this->assertCount(2, $header->types, 'Duplicates should be removed via array_unique');
    }

    public function test_to_string_joins_types_with_comma(): void
    {
        if (!class_exists(AcceptHeaderMediaType::class)) {
            $this->markTestSkipped('AcceptHeaderMediaType class missing, skipping string test.');
        }

        // We depend on AcceptHeaderMediaType::__toString() behaving correctly here.
        $header = new AcceptHeader('text/html', 'application/json');

        $string_val = (string) $header;

        $this->assertStringContainsString('text/html', $string_val);
        $this->assertStringContainsString('application/json', $string_val);
        $this->assertStringContainsString(', ', $string_val);
    }

    private function toBeLessThan(int $expected, int $actual, string $message = ''): void
    {
        $this->assertLessThan($expected, $actual, $message);
    }

    private function toBeGreaterThan(int $expected, int $actual, string $message = ''): void
    {
        $this->assertGreaterThan($expected, $actual, $message);
    }

}
