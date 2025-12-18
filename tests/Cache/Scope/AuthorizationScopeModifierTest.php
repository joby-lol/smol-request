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

class AuthorizationScopeModifierTest extends TestCase
{

    public function test_returns_private_scope_if_authorization_header_is_present(): void
    {
        // 1. Mock Headers to report that 'authorization' exists
        $headers_mock = $this->createMock(Headers::class);
        $headers_mock->method('has')
            ->with('authorization')
            ->willReturn(true);

        // 2. Inject the mock into a real Request
        $request = new Request(
            url: $this->createMock(URL::class),
            method: Method::GET,
            headers: $headers_mock,
            cookies: $this->createMock(Cookies::class),
            post: $this->createMock(Post::class),
            source: $this->createMock(Source::class),
        );

        $modifier = new AuthorizationScopeModifier();

        $this->assertSame(Scope::PRIVATE , $modifier->scope($request));
    }

    public function test_returns_public_scope_if_authorization_header_is_missing(): void
    {
        // 1. Mock Headers to report that 'authorization' is missing
        $headers_mock = $this->createMock(Headers::class);
        $headers_mock->method('has')
            ->with('authorization')
            ->willReturn(false);

        $request = new Request(
            url: $this->createMock(URL::class),
            method: Method::GET,
            headers: $headers_mock,
            cookies: $this->createMock(Cookies::class),
            post: $this->createMock(Post::class),
            source: $this->createMock(Source::class),
        );

        $modifier = new AuthorizationScopeModifier();

        $this->assertSame(Scope::PUBLIC , $modifier->scope($request));
    }

}
