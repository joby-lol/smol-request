<?php

/**
 * smolRequest
 * https://github.com/joby-lol/smol-request
 * (c) 2025 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Request;

/**
 * Global static facade for accessing the current request's data.
 *
 * @internal should be accessed via Request::* methods only
 */
class Current
{
    protected static ?Request $current = null;

    protected static ?RequestFactory $current_factory = null;

    public static function current(): Request
    {
        if (static::$current === null) {
            static::$current = static::currentFactory()->fromGlobals();
        }
        return static::$current;
    }

    public static function setCurrentFactory(RequestFactory $factory): void
    {
        static::$current_factory = $factory;
        static::$current = null;
    }

    public static function currentFactory(): RequestFactory
    {
        if (static::$current_factory === null) {
            static::$current_factory = new RequestFactory();
        }
        return static::$current_factory;
    }
}
