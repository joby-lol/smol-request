<?php

/**
 * smolRequest
 * https://github.com/joby-lol/smol-request
 * (c) 2025 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Request\Cache;

use Joby\Smol\Request\Cache\Keys\AuthorizationKeyModifier;
use Joby\Smol\Request\Cache\Keys\CookieKeyModifier;
use Joby\Smol\Request\Cache\Keys\HeaderKeyModifier;
use Joby\Smol\Request\Cache\Keys\SessionKeyModifier;
use Joby\Smol\Request\Cache\Scope\AuthorizationScopeModifier;
use Joby\Smol\Request\Cache\Scope\CookieScopeModifier;
use Joby\Smol\Request\Cache\Scope\HeaderScopeModifier;
use Joby\Smol\Request\Cache\Scope\SessionScopeModifier;
use Joby\Smol\Request\Request;

/**
 * Global manager for generating cache scopes and IDs for requests.
 */
class Cache
{

    /**
     * @var array<string,ScopeModifier> List of registered scope modifiers.
     */
    public array $scope_modifiers;

    /**
     * @var array<string,KeyModifier> List of registered key modifiers.
     */
    public array $key_modifiers;

    public static self|null $instance = null;

    /**
     * @param array<string,ScopeModifier> $scope_modifiers
     * @param array<string,KeyModifier> $key_modifiers
     */
    public function __construct(
        array $scope_modifiers = [
            'authorization' => new AuthorizationScopeModifier(),
            'cookies'       => new CookieScopeModifier(),
            'headers'       => new HeaderScopeModifier(),
            'session'       => new SessionScopeModifier(),
        ],
        array $key_modifiers = [
            'authorization' => new AuthorizationKeyModifier(),
            'cookies'       => new CookieKeyModifier(),
            'headers'       => new HeaderKeyModifier(),
            'session'       => new SessionKeyModifier(),
        ],
    )
    {
        ksort($scope_modifiers);
        ksort($key_modifiers);
        $this->scope_modifiers = $scope_modifiers;
        $this->key_modifiers   = $key_modifiers;
    }

    public static function instance(): self
    {
        return self::$instance ?? self::$instance = new self;
    }

    public static function scope(Request $request): Scope
    {
        $scope = Scope::PUBLIC ->value;
        foreach (static::instance()->scope_modifiers as $modifier) {
            $scope = min($scope, $modifier->scope($request)->value);
            if ($scope === Scope::NONE->value) {
                break;
            }
        }
        return Scope::from($scope);
    }

    public static function key(Request $request): string
    {
        $key = [];
        foreach (static::instance()->key_modifiers as $modifier) {
            $key[] = $modifier->key($request);
        }
        return hash('sha256', implode('|', $key));
    }

}
