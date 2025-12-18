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
 * Scope modifier that allows certain headers being set to imply different cache scopes. Is configurable at runtime.
 */
class HeaderScopeModifier implements ScopeModifier
{

    /** @var array<string, Scope> Mapping of cookie names to cache scopes */
    public array $header_scopes = [];

    public function scope(Request $request): Scope
    {
        $scope = Scope::PUBLIC ->value;
        foreach ($this->header_scopes as $header_name => $header_scope) {
            if ($request->headers->has($header_name)) {
                $scope = min($scope, $header_scope->value);
                if ($scope === Scope::NONE->value) {
                    break;
                }
            }
        }
        return Scope::from($scope);
    }

}
