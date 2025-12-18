<?php

/**
 * smolRequest
 * https://github.com/joby-lol/smol-request
 * (c) 2025 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Request\Cache\Keys;

use Joby\Smol\Request\Cache\Keys\CookieKeyModifier;
use Joby\Smol\Request\Cookies\Cookies;
use Joby\Smol\Request\Headers\Headers;
use Joby\Smol\Request\Method;
use Joby\Smol\Request\Post\Post;
use Joby\Smol\Request\Request;
use Joby\Smol\Request\Source\Source;
use Joby\Smol\URL\URL;
use PHPUnit\Framework\TestCase;

class CookieKeyModifierTest extends TestCase
{

    public function test_returns_empty_string_when_no_cookies_configured(): void
    {
        $modifier               = new CookieKeyModifier();
        $modifier->cookie_names = [];

        // Request with random cookies
        $request = $this->createRequestWithCookies([
            'random' => 'value',
        ]);

        $this->assertSame('', $modifier->key($request));
    }

    public function test_joins_multiple_cookie_values_with_semicolon(): void
    {
        $modifier               = new CookieKeyModifier();
        $modifier->cookie_names = ['theme', 'session_flavor'];

        $request = $this->createRequestWithCookies([
            'theme'          => 'dark',
            'session_flavor' => 'mint',
        ]);

        // Sorted keys: session_flavor, theme
        // Values: mint, dark
        $this->assertSame('mint;dark', $modifier->key($request));
    }

    public function test_sorts_cookie_names_to_ensure_deterministic_key(): void
    {
        $modifier = new CookieKeyModifier();
        // Configured in REVERSE alphabetical order
        $modifier->cookie_names = ['z_cookie', 'a_cookie'];

        $request = $this->createRequestWithCookies([
            'a_cookie' => 'alpha',
            'z_cookie' => 'omega',
        ]);

        // Should process 'a_cookie' then 'z_cookie'
        $this->assertSame('alpha;omega', $modifier->key($request));
    }

    public function test_handles_missing_cookies_by_using_empty_string(): void
    {
        $modifier               = new CookieKeyModifier();
        $modifier->cookie_names = ['present', 'missing'];

        $request = $this->createRequestWithCookies([
            'present' => 'here',
            // 'missing' is effectively null
        ]);

        // Sorted: missing, present (m comes before p)
        // Values: "" (empty string), "here"
        $this->assertSame(';here', $modifier->key($request));
    }

    public function test_deduplicates_configured_names(): void
    {
        $modifier = new CookieKeyModifier();
        // Duplicate config
        $modifier->cookie_names = ['duplicate', 'duplicate'];

        $request = $this->createRequestWithCookies([
            'duplicate' => 'val',
        ]);

        // Should appear only once
        $this->assertSame('val', $modifier->key($request));
    }

    // -- Helper --

    private function createRequestWithCookies(array $cookie_data): Request
    {
        return new Request(
            url: $this->createMock(URL::class),
            method: Method::GET,
            headers: $this->createMock(Headers::class),
            cookies: new Cookies($cookie_data),
            post: $this->createMock(Post::class),
            source: $this->createMock(Source::class),
        );
    }

}
