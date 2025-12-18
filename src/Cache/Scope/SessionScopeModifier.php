<?php

/**
 * smolRequest
 * https://github.com/joby-lol/smol-request
 * (c) 2025 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Request\Cache\Scope;

use Joby\Smol\Request\Cache\Scope;
use Joby\Smol\Request\Cache\ScopeModifier;
use Joby\Smol\Request\Request;

/**
 * Scope modifier that sets the cache scope to PRIVATE if a session cookie is present.
 */
class SessionScopeModifier implements ScopeModifier
{
    public function scope(Request $request): Scope
    {
        // @phpstan-ignore-next-line when not setting session_name() always returns a string
        if ($request->cookies->has(session_name())) {
            return Scope::PRIVATE;
        }
        return Scope::PUBLIC;
    }
}
