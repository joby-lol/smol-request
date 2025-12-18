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
 * Key modifier that varies the cache key based on the session cookie value.
 */
class SessionKeyModifier implements KeyModifier
{
    public function key(Request $request): string
    {
        // @phpstan-ignore-next-line when not setting session_name() always returns a string
        return $request->cookies->get(session_name()) ?? '';
    }
}
