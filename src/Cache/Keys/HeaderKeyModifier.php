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
 * Key modifier that varies the cache key based on particular header values, which are configurable at runtime.
 */
class HeaderKeyModifier implements KeyModifier
{

    /** @var array<string> list of cookie names to vary the cache key by */
    public array $header_names = [];

    public function key(Request $request): string
    {
        sort($this->header_names);
        $key = [];
        foreach (array_unique($this->header_names) as $name) {
            $key[] = $request->headers->get($name) ?? '';
        }
        return implode(';', $key);
    }

}
