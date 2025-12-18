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
 * Scope modifier that allows certain cookies being set to imply different cache scopes. Is configurable at runtime.
 */
class CookieScopeModifier implements ScopeModifier
{
    /** @var array<string, Scope> Mapping of cookie names to cache scopes */
    public array $cookie_scopes = [];

    public function scope(Request $request): Scope
    {
        $scope = Scope::PUBLIC ->value;
        foreach ($this->cookie_scopes as $cookie_name => $cookie_scope) {
            if ($request->cookies->has($cookie_name)) {
                $scope = min($scope, $cookie_scope->value);
                if ($scope === Scope::NONE->value) {
                    break;
                }
            }
        }
        return Scope::from($scope);
    }
}
