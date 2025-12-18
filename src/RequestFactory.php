<?php

/**
 * smolRequest
 * https://github.com/joby-lol/smol-request
 * (c) 2025 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Request;

use Joby\Smol\Request\Cookies\CookiesFactory;
use Joby\Smol\Request\Headers\HeadersFactory;
use Joby\Smol\Request\Post\PostFactory;
use Joby\Smol\Request\Source\SourceFactory;
use Joby\Smol\URL\URL;
use Joby\Smol\URL\UrlFactory;
use Joby\Smol\URL\UrlFactoryInterface;

/**
 * @codeCoverageIgnore this class is just wiring together other factories
 */
readonly class RequestFactory
{
    /**
     * @param UrlFactoryInterface<URL> $url_factory
     */
    public function __construct(
        public UrlFactoryInterface $url_factory = new UrlFactory(),
        public HeadersFactory $headers_factory = new HeadersFactory(),
        public CookiesFactory $cookies_factory = new CookiesFactory(),
        public PostFactory $post_factory = new PostFactory(),
        public SourceFactory $source_factory = new SourceFactory(),
    ) {
    }

    public function fromGlobals(): Request
    {
        return new Request(
            $this->url_factory->fromGlobals(),
            // @phpstan-ignore-next-line we're trusting that $_SERVER['REQUEST_METHOD'] is set and valid, and if it isn't that's a good time to error out
            Method::tryFrom($_SERVER['REQUEST_METHOD']) ?? Method::GET,
            $this->headers_factory->fromGlobals(),
            $this->cookies_factory->fromGlobals(),
            $this->post_factory->fromGlobals(),
            $this->source_factory->fromGlobals(),
        );
    }
}
