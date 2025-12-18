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
 * Key modifier that varies the cache key based on particular cookie values, which are configurable at runtime.
 */
class CookieKeyModifier implements KeyModifier
{

    /** @var array<string> list of cookie names to vary the cache key by */
    public array $cookie_names = [];

    public function key(Request $request): string
    {
        sort($this->cookie_names);
        $key = [];
        foreach (array_unique($this->cookie_names) as $name) {
            $key[] = $request->cookies->get($name) ?? '';
        }
        return implode(';', $key);
    }

}
