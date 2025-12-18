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

class CookieScopeModifierTest extends TestCase
{

    public function test_defaults_to_public_when_no_scopes_configured(): void
    {
        $modifier                = new CookieScopeModifier();
        $modifier->cookie_scopes = [];

        $request = $this->createRequestWithCookies([]);

        $this->assertSame(Scope::PUBLIC , $modifier->scope($request));
    }

    public function test_defaults_to_public_when_cookies_are_missing(): void
    {
        $modifier                = new CookieScopeModifier();
        $modifier->cookie_scopes = [
            'auth_token' => Scope::PRIVATE ,
        ];

        // Request has unrelated cookie, but not 'auth_token'
        $request = $this->createRequestWithCookies([
            'tracking_id' => '123',
        ]);

        $this->assertSame(Scope::PUBLIC , $modifier->scope($request));
    }

    public function test_returns_mapped_scope_when_cookie_is_present(): void
    {
        $modifier                = new CookieScopeModifier();
        $modifier->cookie_scopes = [
            'auth_token' => Scope::PRIVATE ,
        ];

        $request = $this->createRequestWithCookies([
            'auth_token' => 'secret_value',
        ]);

        $this->assertSame(Scope::PRIVATE , $modifier->scope($request));
    }

    public function test_cookie_names_are_case_sensitive(): void
    {
        /*
         * Verify that because Cookies class is case-sensitive,
         * 'AuthToken' does NOT trigger the scope for 'authtoken'.
         */
        $modifier                = new CookieScopeModifier();
        $modifier->cookie_scopes = [
            'authtoken' => Scope::PRIVATE ,
        ];

        // Request has 'AuthToken' (CamelCase)
        $request = $this->createRequestWithCookies([
            'AuthToken' => 'secret_value',
        ]);

        // Should return PUBLIC because 'authtoken' (lowercase) wasn't found
        $this->assertSame(Scope::PUBLIC , $modifier->scope($request));
    }

    public function test_picks_lowest_scope_when_multiple_configured_cookies_match(): void
    {
        $modifier                = new CookieScopeModifier();
        $modifier->cookie_scopes = [
            'weak_pref'   => Scope::PRIVATE ,
            'strict_auth' => Scope::NONE, // NONE is lower (stricter) than PRIVATE
        ];

        // Request contains BOTH cookies
        $request = $this->createRequestWithCookies([
            'weak_pref'   => 'dark_mode',
            'strict_auth' => 'super_secret',
        ]);

        // Logic should use min() to pick the stricter one (NONE)
        $this->assertSame(Scope::NONE, $modifier->scope($request));
    }

    /**
     * Helper to create a fully instantiated Request with specific cookies.
     * @param array<string, string> $cookie_data Key-value pairs for cookies
     */
    private function createRequestWithCookies(array $cookie_data): Request
    {
        $cookies = new Cookies($cookie_data);
        return new Request(
            url: $this->createMock(URL::class),
            method: Method::GET,
            headers: $this->createMock(Headers::class),
            cookies: $cookies,
            post: $this->createMock(Post::class),
            source: $this->createMock(Source::class),
        );
    }

}
