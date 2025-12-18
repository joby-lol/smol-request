<?php

/**
 * smolRequest
 * https://github.com/joby-lol/smol-request
 * (c) 2025 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Request\Cache\Keys;

use Joby\Smol\Request\Cache\Keys\AuthorizationKeyModifier;
use Joby\Smol\Request\Cookies\Cookies;
use Joby\Smol\Request\Headers\HeaderInterface;
use Joby\Smol\Request\Headers\Headers;
use Joby\Smol\Request\Method;
use Joby\Smol\Request\Post\Post;
use Joby\Smol\Request\Request;
use Joby\Smol\Request\Source\Source;
use Joby\Smol\URL\URL;
use PHPUnit\Framework\TestCase;

class AuthorizationKeyModifierTest extends TestCase
{

    public function test_returns_authorization_header_value_when_present(): void
    {
        // 1. Mock the header object that has a __toString method
        $auth_header = $this->createMock(HeaderInterface::class);
        $auth_header->method('__toString')
            ->willReturn('Bearer some_secret_token');

        // 2. Mock Headers container to return that object
        $headers_mock = $this->createMock(Headers::class);
        $headers_mock->method('get')
            ->with('authorization')
            ->willReturn($auth_header);

        $request  = $this->createRequestWithHeaders($headers_mock);
        $modifier = new AuthorizationKeyModifier();

        $this->assertSame('Bearer some_secret_token', $modifier->key($request));
    }

    public function test_returns_empty_string_when_authorization_header_is_missing(): void
    {
        // 1. Mock Headers to return null ( simulating missing header )
        $headers_mock = $this->createMock(Headers::class);
        $headers_mock->method('get')
            ->with('authorization')
            ->willReturn(null);

        $request  = $this->createRequestWithHeaders($headers_mock);
        $modifier = new AuthorizationKeyModifier();

        // The code `get(...)?->__toString() ?? ''` should result in ''
        $this->assertSame('', $modifier->key($request));
    }

    /**
     * Helper to keep the test methods clean
     */
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
