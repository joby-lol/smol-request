<?php

/**
 * smolRequest
 * https://github.com/joby-lol/smol-request
 * (c) 2025 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Request\Cookies;

use PHPUnit\Framework\TestCase;

class CookiesTest extends TestCase
{
    public function test_get_require(): void
    {
        $cookies = new Cookies([
            'username' => 'john_doe',
            'theme' => 'dark',
        ]);

        $this->assertEquals('john_doe', $cookies->get('username'));
        $this->assertEquals('dark', $cookies->get('theme'));
        $this->assertNull($cookies->get('nonexistent'));
        $this->assertEquals('light', $cookies->get('nonexistent', 'light'));

        $this->assertEquals('john_doe', $cookies->require('username'));

        $this->expectException(CookieException::class);
        $cookies->require('nonexistent');
    }

    public function test_get_require_int(): void
    {
        $cookies = new Cookies([
            'user_id' => '12345',
            'max_sessions' => '5',
            'invalid' => 'abc',
        ]);

        $this->assertEquals(12345, $cookies->getInt('user_id'));
        $this->assertEquals(5, $cookies->getInt('max_sessions'));
        $this->assertNull($cookies->getInt('nonexistent'));
        $this->assertEquals(1, $cookies->getInt('nonexistent', 1));

        $this->assertEquals(12345, $cookies->requireInt('user_id'));

        $this->expectException(CookieException::class);
        $cookies->requireInt('nonexistent');

        $this->expectException(CookieException::class);
        $cookies->getInt('invalid');
    }

    public function test_get_require_bool(): void
    {
        $cookies = new Cookies([
            'remember_me' => '1',
            'notifications' => 'false',
            'tracking' => 'yes',
            'invalid' => 'maybe',
        ]);

        $this->assertTrue($cookies->getBool('remember_me'));
        $this->assertFalse($cookies->getBool('notifications'));
        $this->assertTrue($cookies->getBool('tracking'));
        $this->assertNull($cookies->getBool('nonexistent'));

        $this->assertTrue($cookies->requireBool('remember_me'));

        $this->expectException(CookieException::class);
        $cookies->requireBool('nonexistent');

        $this->expectException(CookieException::class);
        $cookies->getBool('invalid');
    }

    public function test_get_require_float(): void
    {
        $cookies = new Cookies([
            'rating' => '4.5',
            'discount' => '0.15',
            'invalid' => 'abc',
        ]);

        $this->assertEquals(4.5, $cookies->getFloat('rating'));
        $this->assertEquals(0.15, $cookies->getFloat('discount'));
        $this->assertNull($cookies->getFloat('nonexistent'));
        $this->assertEquals(3.0, $cookies->getFloat('nonexistent', 3.0));

        $this->assertEquals(4.5, $cookies->requireFloat('rating'));

        $this->expectException(CookieException::class);
        $cookies->requireFloat('nonexistent');

        $this->expectException(CookieException::class);
        $cookies->getFloat('invalid');
    }

    public function test_has(): void
    {
        $cookies = new Cookies([
            'session_id' => 'abc123',
            'language' => 'en',
        ]);

        $this->assertTrue($cookies->has('session_id'));
        $this->assertTrue($cookies->has('language'));
        $this->assertFalse($cookies->has('nonexistent'));
    }

    public function test_type_conversion(): void
    {
        $cookies = new Cookies([
            'bool_true' => true,
            'bool_false' => false,
            'number' => 42,
            'decimal' => 3.14,
            'null_value' => null,
        ]);

        $this->assertEquals('true', $cookies->get('bool_true'));
        $this->assertEquals('false', $cookies->get('bool_false'));
        $this->assertEquals('42', $cookies->get('number'));
        $this->assertEquals('3.14', $cookies->get('decimal'));
        $this->assertFalse($cookies->has('null_value'));
    }

    public function test_invalid_cookie_key(): void
    {
        $this->expectException(CookieException::class);
        new Cookies([
            123 => 'invalid_key', // @phpstan-ignore-line
        ]);
    }

    public function test_invalid_cookie_value_type(): void
    {
        $this->expectException(CookieException::class);
        new Cookies([
            'invalid' => ['array' => 'value'],
        ]);
    }
}
