<?php

/**
 * smolRequest
 * https://github.com/joby-lol/smol-request
 * (c) 2025 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Request\Cache\Keys;

use Joby\Smol\Request\Cache\Keys\HeaderKeyModifier;
use Joby\Smol\Request\Cookies\Cookies;
use Joby\Smol\Request\Headers\Headers;
use Joby\Smol\Request\Headers\HeaderInterface;
use Joby\Smol\Request\Method;
use Joby\Smol\Request\Post\Post;
use Joby\Smol\Request\Request;
use Joby\Smol\Request\Source\Source;
use Joby\Smol\URL\URL;
use PHPUnit\Framework\TestCase;

class HeaderKeyModifierTest extends TestCase
{

    public function test_returns_empty_string_when_no_headers_configured(): void
    {
        $modifier               = new HeaderKeyModifier();
        $modifier->header_names = [];

        // Headers mock that should never be called
        $headers_mock = $this->createMock(Headers::class);
        $headers_mock->expects($this->never())->method('get');

        $request = $this->createRequestWithHeaders($headers_mock);

        $this->assertSame('', $modifier->key($request));
    }

    public function test_joins_multiple_header_values_with_semicolon(): void
    {
        $modifier               = new HeaderKeyModifier();
        $modifier->header_names = ['x-foo', 'x-bar'];

        // Mock 2 headers existing
        $headers_mock = $this->createMock(Headers::class);
        $headers_mock->method('get')
            ->willReturnMap([
                ['x-foo', $this->createMockStringableHeader('value_foo')],
                ['x-bar', $this->createMockStringableHeader('value_bar')],
            ]);

        $request = $this->createRequestWithHeaders($headers_mock);

        // Expect: "value_bar;value_foo" (sorted alphabetically) OR "value_foo;value_bar"
        // Logic: sort($names) -> bar, foo. Result: value_bar;value_foo.
        $this->assertSame('value_bar;value_foo', $modifier->key($request));
    }

    public function test_sorts_header_names_to_ensure_deterministic_key(): void
    {
        /*
         * Ideally, ['A', 'B'] and ['B', 'A'] should produce the same cache key.
         * The class uses sort() to enforce this.
         */
        $modifier = new HeaderKeyModifier();

        // Pass in REVERSE alphabetical order
        $modifier->header_names = ['x-second', 'x-first'];

        $headers_mock = $this->createMock(Headers::class);
        $headers_mock->method('get')
            ->willReturnMap([
                ['x-first', $this->createMockStringableHeader('1')],
                ['x-second', $this->createMockStringableHeader('2')],
            ]);

        $request = $this->createRequestWithHeaders($headers_mock);

        // 'x-first' sorts before 'x-second', so '1' comes before '2'
        $this->assertSame('1;2', $modifier->key($request));
    }

    public function test_handles_missing_headers_by_using_empty_string(): void
    {
        $modifier               = new HeaderKeyModifier();
        $modifier->header_names = ['x-existing', 'x-missing'];

        $headers_mock = $this->createMock(Headers::class);
        $headers_mock->method('get')
            ->willReturnMap([
                ['x-existing', $this->createMockStringableHeader('found')],
                // 'x-missing' is not mapped, implies return null (default mock behavior)
                // or we can explicitly map it to null if strict types are off in mock configuration.
                ['x-missing', null],
            ]);

        $request = $this->createRequestWithHeaders($headers_mock);

        // Sorted: x-existing, x-missing -> "found;"
        $this->assertSame('found;', $modifier->key($request));
    }

    public function test_deduplicates_configured_names(): void
    {
        $modifier = new HeaderKeyModifier();
        // Duplicate config
        $modifier->header_names = ['x-foo', 'x-foo'];

        $headers_mock = $this->createMock(Headers::class);
        // Should only be called ONCE for 'x-foo'
        $headers_mock->expects($this->once())
            ->method('get')
            ->with('x-foo')
            ->willReturn($this->createMockStringableHeader('val'));

        $request = $this->createRequestWithHeaders($headers_mock);

        $this->assertSame('val', $modifier->key($request));
    }

    // -- Helpers --

    private function createRequestWithHeaders(Headers $headers): Request
    {
        return new Request(
            url: $this->createMock(URL::class),
            method: Method::GET,
            headers: $headers,
            cookies: $this->createMock(Cookies::class),
            post: $this->createMock(Post::class),
            source: $this->createMock(Source::class),
        );
    }

    /**
     * Creates a dummy object that implements HeaderInterface (if it exists)
     * and __toString.
     */
    private function createMockStringableHeader(string $value)
    {
        // Assuming Headers::get returns something that implements HeaderInterface
        // or acts stringable.
        $mock = $this->createMock(HeaderInterface::class);
        $mock->method('__toString')->willReturn($value);
        return $mock;
    }

}
