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
 * Key modifier that varies the cache key based on the current request's path.
 */
class PathKeyModifier implements KeyModifier
{

    public function key(Request $request): string
    {
        return (string) $request->url->path;
    }

}
