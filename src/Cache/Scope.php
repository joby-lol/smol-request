<?php

/**
 * smolRequest
 * https://github.com/joby-lol/smol-request
 * (c) 2025 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Request\Cache;

/**
 * Enum for the different cache scopes that a response can have. For ease of comparison and future expansion, they are
 * backed by integers so that they can be easily compared. Generally when determining scope from multiple sources, the
 * least restrictive value should be used, which is equivalent to the lower value.
 */
enum Scope: int
{
    /**
     * Indicates that a response contains public data that may be cached by any client and shared by multiple users.
     * Responses should be cacheable by reverse proxies, browsers, and internal cache systems.
     */
    case PUBLIC = 2;
    /**
     * Indicates that a response contains private data that should only be cached by the exact browser session making
     * the request. Responses should not be cacheable by reverse proxies or internal systems, but should be cacheable
     * by browsers.
     */
    case PRIVATE = 1;
    /**
     * Indicates that a response is not cacheable. It should not be cached by any system.
     */
    case NONE = 0;
}
