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
use Joby\Smol\Request\Headers\GenericHeader;
use Joby\Smol\Request\Headers\Headers;
use Joby\Smol\Request\Method;
use Joby\Smol\Request\Post\Post;
use Joby\Smol\Request\Request;
use Joby\Smol\Request\Source\Source;
use Joby\Smol\URL\URL;
use PHPUnit\Framework\TestCase;

class SessionScopeModifierTest extends TestCase
{

    private string $original_session_name;

    protected function setUp(): void
    {
        $this->original_session_name = session_name();
    }

    protected function tearDown(): void
    {
        session_name($this->original_session_name);
    }

    public function test_returns_private_scope_if_session_cookie_is_present(): void
    {
        session_name('PHPSESSID');

        // 1. Create real Cookies object
        // Note: Ensure Cookies class handles 'PHPSESSID' case-insensitivity correctly
        $cookies = new Cookies([
            'PHPSESSID' => $this->createMock(GenericHeader::class),
        ]);

        // 2. Create a real Request using named arguments for clarity
        $request = new Request(
            url: $this->createMock(URL::class),
            method: Method::GET,
            headers: $this->createMock(Headers::class),
            cookies: $cookies,
            post: $this->createMock(Post::class),
            source: $this->createMock(Source::class),
        );

        $modifier = new SessionScopeModifier();

        // Use assertSame for Enums
        $this->assertSame(Scope::PRIVATE , $modifier->scope($request));
    }

    public function test_returns_public_scope_if_session_cookie_is_missing(): void
    {
        session_name('PHPSESSID');

        $cookies = new Cookies([]);

        $request = new Request(
            url: $this->createMock(URL::class),
            method: Method::GET,
            headers: $this->createMock(Headers::class),
            cookies: $cookies,
            post: $this->createMock(Post::class),
            source: $this->createMock(Source::class),
        );

        $modifier = new SessionScopeModifier();

        $this->assertSame(Scope::PUBLIC , $modifier->scope($request));
    }

}
