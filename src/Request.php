<?php

/**
 * smolRequest
 * https://github.com/joby-lol/smol-request
 * (c) 2025 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Request;

use Joby\Smol\Request\Cache\Cache;
use Joby\Smol\Request\Cache\Scope;
use Joby\Smol\Request\Cookies\Cookies;
use Joby\Smol\Request\Headers\Headers;
use Joby\Smol\Request\Post\Post;
use Joby\Smol\Request\Source\Source;
use Joby\Smol\URL\URL;

/**
 * Global static facade for accessing the current request's data.
 */
readonly class Request
{
    public static function current(): Request
    {
        return Current::current();
    }

    public static function setCurrentFactory(RequestFactory $factory): void
    {
        Current::setCurrentFactory($factory);
    }

    public static function currentFactory(): RequestFactory
    {
        return Current::currentFactory();
    }

    public function __construct(
        public URL $url,
        public Method $method,
        public Headers $headers,
        public Cookies $cookies,
        public Post $post,
        public Source $source,
    ) {
    }

    /**
     * @codeCoverageIgnore just passes through to Cache class
     */
    public function cacheScope(): Scope
    {
        return Cache::scope($this);
    }

    /**
     * @codeCoverageIgnore just passes through to Cache class
     */
    public function cacheKey(): string
    {
        return Cache::key($this);
    }

    public function with(
        ?URL $url = null,
        ?Method $method = null,
        ?Headers $headers = null,
        ?Cookies $cookies = null,
        ?Post $post = null,
        ?Source $source = null,
    ): self {
        if (
            $url === null
            && $method === null
            && $headers === null
            && $cookies === null
            && $post === null
            && $source === null
        ) {
            return $this;
        }
        return new self(
            $url ?? $this->url,
            $method ?? $this->method,
            $headers ?? $this->headers,
            $cookies ?? $this->cookies,
            $post ?? $this->post,
            $source ?? $this->source,
        );
    }
}
