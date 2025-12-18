<?php

/**
 * smolRequest
 * https://github.com/joby-lol/smol-request
 * (c) 2025 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Request;

use Joby\Smol\Request\Cookies\Cookies;
use Joby\Smol\Request\Headers\Headers;
use Joby\Smol\Request\Post\Post;
use Joby\Smol\Request\Source\Source;
use Joby\Smol\URL\URL;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
    public function test_current_returns_some_instance(): void
    {
        $request = Request::current();
        $this->assertInstanceOf(Request::class, $request);
    }

    public function test_configuring_factory(): void
    {
        $mockFactory = $this->createMock(RequestFactory::class);
        Request::setCurrentFactory($mockFactory);
        $this->assertSame($mockFactory, Request::currentFactory());
    }
    public function test_with_method_creates_modified_copy_url(): void
    {
        $a = new Request(
            $this->createMock(URL::class),
            Method::GET,
            $this->createMock(Headers::class),
            $this->createMock(Cookies::class),
            $this->createMock(Post::class),
            $this->createMock(Source::class),
        );
        $new_url = $this->createMock(URL::class);
        $b = $a->with(url: $new_url);
        $this->assertSame($new_url, $b->url);
        $this->assertSame($a->method, $b->method);
        $this->assertSame($a->headers, $b->headers);
        $this->assertSame($a->cookies, $b->cookies);
        $this->assertSame($a->post, $b->post);
        $this->assertSame($a->source, $b->source);
    }

    public function test_with_method_creates_modified_copy_method(): void
    {
        $a = new Request(
            $this->createMock(URL::class),
            Method::GET,
            $this->createMock(Headers::class),
            $this->createMock(Cookies::class),
            $this->createMock(Post::class),
            $this->createMock(Source::class),
        );
        $new_method = Method::POST;
        $b = $a->with(method: $new_method);
        $this->assertSame($a->url, $b->url);
        $this->assertSame($new_method, $b->method);
        $this->assertSame($a->headers, $b->headers);
        $this->assertSame($a->cookies, $b->cookies);
        $this->assertSame($a->post, $b->post);
        $this->assertSame($a->source, $b->source);
    }

    public function test_with_method_creates_modified_copy_headers(): void
    {
        $a = new Request(
            $this->createMock(URL::class),
            Method::GET,
            $this->createMock(Headers::class),
            $this->createMock(Cookies::class),
            $this->createMock(Post::class),
            $this->createMock(Source::class),
        );
        $new_headers = $this->createMock(Headers::class);
        $b = $a->with(headers: $new_headers);
        $this->assertSame($a->url, $b->url);
        $this->assertSame($a->method, $b->method);
        $this->assertSame($new_headers, $b->headers);
        $this->assertSame($a->cookies, $b->cookies);
        $this->assertSame($a->post, $b->post);
        $this->assertSame($a->source, $b->source);
    }

    public function test_with_method_creates_modified_copy_cookies(): void
    {
        $a = new Request(
            $this->createMock(URL::class),
            Method::GET,
            $this->createMock(Headers::class),
            $this->createMock(Cookies::class),
            $this->createMock(Post::class),
            $this->createMock(Source::class),
        );
        $new_cookies = $this->createMock(Cookies::class);
        $b = $a->with(cookies: $new_cookies);
        $this->assertSame($a->url, $b->url);
        $this->assertSame($a->method, $b->method);
        $this->assertSame($a->headers, $b->headers);
        $this->assertSame($new_cookies, $b->cookies);
        $this->assertSame($a->post, $b->post);
        $this->assertSame($a->source, $b->source);
    }

    public function test_with_method_creates_modified_copy_post(): void
    {
        $a = new Request(
            $this->createMock(URL::class),
            Method::GET,
            $this->createMock(Headers::class),
            $this->createMock(Cookies::class),
            $this->createMock(Post::class),
            $this->createMock(Source::class),
        );
        $new_post = $this->createMock(Post::class);
        $b = $a->with(post: $new_post);
        $this->assertSame($a->url, $b->url);
        $this->assertSame($a->method, $b->method);
        $this->assertSame($a->headers, $b->headers);
        $this->assertSame($a->cookies, $b->cookies);
        $this->assertSame($new_post, $b->post);
        $this->assertSame($a->source, $b->source);
    }

    public function test_with_method_creates_modified_copy_source(): void
    {
        $a = new Request(
            $this->createMock(URL::class),
            Method::GET,
            $this->createMock(Headers::class),
            $this->createMock(Cookies::class),
            $this->createMock(Post::class),
            $this->createMock(Source::class),
        );
        $new_source = $this->createMock(Source::class);
        $b = $a->with(source: $new_source);
        $this->assertSame($a->url, $b->url);
        $this->assertSame($a->method, $b->method);
        $this->assertSame($a->headers, $b->headers);
        $this->assertSame($a->cookies, $b->cookies);
        $this->assertSame($a->post, $b->post);
        $this->assertSame($new_source, $b->source);
    }

    public function test_with_without_arguments_returns_identical_copy(): void
    {
        $a = new Request(
            $this->createMock(URL::class),
            Method::GET,
            $this->createMock(Headers::class),
            $this->createMock(Cookies::class),
            $this->createMock(Post::class),
            $this->createMock(Source::class),
        );
        $b = $a->with();
        $this->assertSame($a, $b);
        $this->assertSame($a->url, $b->url);
        $this->assertSame($a->method, $b->method);
        $this->assertSame($a->headers, $b->headers);
        $this->assertSame($a->cookies, $b->cookies);
        $this->assertSame($a->post, $b->post);
        $this->assertSame($a->source, $b->source);
    }
}
