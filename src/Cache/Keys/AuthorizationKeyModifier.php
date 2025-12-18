<?php

/**
 * smolRequest
 * https://github.com/joby-lol/smol-request
 * (c) 2025 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Request\Cache\Keys;

use Joby\Smol\Request\Cache\KeyModifier;
use Joby\Smol\Request\Request;

/**
 * Key modifier that varies the cache key based on the Authorization header value.
 */
class AuthorizationKeyModifier implements KeyModifier
{

    public function key(Request $request): string
    {
        return $request->headers->get('authorization')?->__toString()
            ?? '';
    }

}
