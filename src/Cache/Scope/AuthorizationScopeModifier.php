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
 * Scope modifier that sets the cache scope to PRIVATE if an Authorization header is present.
 */
class AuthorizationScopeModifier implements ScopeModifier
{
    public function scope(Request $request): Scope
    {
        if ($request->headers->has('authorization')) {
            return Scope::PRIVATE;
        }
        return Scope::PUBLIC;
    }
}
