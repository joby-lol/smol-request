<?php

/**
 * smolRequest
 * https://github.com/joby-lol/smol-request
 * (c) 2025 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Request\Cache;

use Joby\Smol\Request\Request;

interface ScopeModifier
{
    public function scope(Request $request): Scope;
}
