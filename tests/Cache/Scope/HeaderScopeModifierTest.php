<?php

/**
 * smolRequest
 * https://github.com/joby-lol/smol-request
 * (c) 2025 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Request\Cache\Scope;

use Joby\Smol\Request\Cache\Scope;
use Joby\Smol\Request\Cookies\Cookies;
use Joby\Smol\Request\Headers\Headers;
use Joby\Smol\Request\Method;
use Joby\Smol\Request\Post\Post;
use Joby\Smol\Request\Request;
use Joby\Smol\Request\Source\Source;
use Joby\Smol\URL\URL;
use PHPUnit\Framework\TestCase;

class HeaderScopeModifierTest extends TestCase
{

    public function test_defaults_to_public_when_no_scopes_configured(): void
    {
        $modifier                = new HeaderScopeModifier();
        $modifier->header_scopes = [];

        // Mock headers to return false for everything (safeguard)
        $headers_mock = $this->createMock(Headers::class);
        $headers_mock->method('has')->willReturn(false);

        $request = $this->createRequestWithHeaders($headers_mock);

        $this->assertSame(Scope::PUBLIC , $modifier->scope($request));
    }

    public function test_defaults_to_public_when_configured_headers_are_missing(): void
    {
        $modifier                = new HeaderScopeModifier();
        $modifier->header_scopes = [
            'x-sensitive-data' => Scope::PRIVATE ,
        ];

        // Mock headers to say "no" to 'x-sensitive-data'
        $headers_mock = $this->createMock(Headers::class);
        $headers_mock->method('has')
            ->willReturnMap([
                ['x-sensitive-data', false],
            ]);

        $request = $this->createRequestWithHeaders($headers_mock);

        $this->assertSame(Scope::PUBLIC , $modifier->scope($request));
    }

    public function test_returns_mapped_scope_when_header_is_present(): void
    {
        $modifier                = new HeaderScopeModifier();
        $modifier->header_scopes = [
            'x-my-header' => Scope::PRIVATE ,
        ];

        // Mock headers to say "yes" to 'x-my-header'
        $headers_mock = $this->createMock(Headers::class);
        $headers_mock->method('has')
            ->willReturnMap([
                ['x-my-header', true],
            ]);

        $request = $this->createRequestWithHeaders($headers_mock);

        $this->assertSame(Scope::PRIVATE , $modifier->scope($request));
    }

    public function test_picks_lowest_scope_when_multiple_headers_match(): void
    {
        $modifier                = new HeaderScopeModifier();
        $modifier->header_scopes = [
            'x-weak'   => Scope::PRIVATE ,
            'x-strict' => Scope::NONE, // Stricter/lower value
        ];

        // Mock headers to say "yes" to BOTH
        $headers_mock = $this->createMock(Headers::class);
        $headers_mock->method('has')
            ->willReturnMap([
                ['x-weak', true],
                ['x-strict', true],
            ]);

        $request = $this->createRequestWithHeaders($headers_mock);

        // Logic should use min() to pick the stricter one (NONE)
        $this->assertSame(Scope::NONE, $modifier->scope($request));
    }

    // -- Helper --

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

}
