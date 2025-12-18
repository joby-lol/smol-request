<?php

/**
 * smolRequest
 * https://github.com/joby-lol/smol-request
 * (c) 2025 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Request\Cache\Keys;

use Joby\Smol\Request\Cache\Keys\SessionKeyModifier;
use Joby\Smol\Request\Cookies\Cookies;
use Joby\Smol\Request\Headers\Headers;
use Joby\Smol\Request\Method;
use Joby\Smol\Request\Post\Post;
use Joby\Smol\Request\Request;
use Joby\Smol\Request\Source\Source;
use Joby\Smol\URL\URL;
use PHPUnit\Framework\TestCase;

class SessionKeyModifierTest extends TestCase
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

    public function test_returns_session_id_when_cookie_present(): void
    {
        session_name('PHPSESSID');

        // 1. Create real Cookies with the session ID
        $cookies = new Cookies([
            'PHPSESSID' => 'abc_123_session_value',
        ]);

        // 2. Create Request
        $request = new Request(
            url: $this->createMock(URL::class),
            method: Method::GET,
            headers: $this->createMock(Headers::class),
            cookies: $cookies,
            post: $this->createMock(Post::class),
            source: $this->createMock(Source::class),
        );

        $modifier = new SessionKeyModifier();

        $this->assertSame('abc_123_session_value', $modifier->key($request));
    }

    public function test_returns_empty_string_when_cookie_missing(): void
    {
        session_name('PHPSESSID');

        // Cookies exist, but not the session one
        $cookies = new Cookies([
            'other_cookie' => 'foo',
        ]);

        $request = new Request(
            url: $this->createMock(URL::class),
            method: Method::GET,
            headers: $this->createMock(Headers::class),
            cookies: $cookies,
            post: $this->createMock(Post::class),
            source: $this->createMock(Source::class),
        );

        $modifier = new SessionKeyModifier();

        $this->assertSame('', $modifier->key($request));
    }

    public function test_respects_custom_session_name(): void
    {
        // Change global session name to something non-standard
        session_name('MY_CUSTOM_APP_ID');

        $cookies = new Cookies([
            'MY_CUSTOM_APP_ID' => 'custom_session_value',
            'PHPSESSID'        => 'ignored_value', // Default name should be ignored now
        ]);

        $request = new Request(
            url: $this->createMock(URL::class),
            method: Method::GET,
            headers: $this->createMock(Headers::class),
            cookies: $cookies,
            post: $this->createMock(Post::class),
            source: $this->createMock(Source::class),
        );

        $modifier = new SessionKeyModifier();

        $this->assertSame('custom_session_value', $modifier->key($request));
    }

}
