<?php

/**
 * smolRequest
 * https://github.com/joby-lol/smol-request
 * (c) 2025 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Request\Source;

/**
 * Represents the "source" of a request, including the IP address of the client. The "client" will be the actual client IP as forwarded by proxies if the server is behind a proxy and the factory determines that the actual (i.e. proxy) IP can be trusted about who it is forwarding for. The "actual" IP is the direct connection IP address.
 */
readonly class Source
{
    public function __construct(
        public string $client,
        public string $actual,
    ) {
    }
}
