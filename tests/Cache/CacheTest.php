<?php

/**
 * smolRequest
 * https://github.com/joby-lol/smol-request
 * (c) 2025 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Request\Cache;

use Joby\Smol\Request\Cache\Cache;
use Joby\Smol\Request\Cache\Scope;
use Joby\Smol\Request\Cache\ScopeModifier;
use Joby\Smol\Request\Cache\KeyModifier;
use Joby\Smol\Request\Request;
use PHPUnit\Framework\TestCase;

class CacheTest extends TestCase
{

    protected function tearDown(): void
    {
        // Vital: Clear the singleton instance after every test to prevent 
        // pollution of global state.
        Cache::$instance = null;
    }

    // -- Scope Tests --

    public function test_scope_defaults_to_public_when_no_modifiers_exist(): void
    {
        // Inject a Cache instance with empty modifiers
        Cache::$instance = new Cache(
            scope_modifiers: [],
            key_modifiers: [],
        );

        $request = $this->createMock(Request::class);
        $result  = Cache::scope($request);

        $this->assertSame(Scope::PUBLIC , $result);
    }

    public function test_scope_returns_lowest_value_among_modifiers(): void
    {
        $request = $this->createMock(Request::class);

        $mod_public = $this->createMock(ScopeModifier::class);
        $mod_public->method('scope')->willReturn(Scope::PUBLIC);

        $mod_private = $this->createMock(ScopeModifier::class);
        $mod_private->method('scope')->willReturn(Scope::PRIVATE);

        // Inject instance with mocks
        Cache::$instance = new Cache(
            scope_modifiers: [
                'a' => $mod_public,
                'b' => $mod_private,
            ],
        );

        // Expect PRIVATE (lowest wins)
        $this->assertSame(Scope::PRIVATE , Cache::scope($request));
    }

    public function test_scope_short_circuits_on_none(): void
    {
        $request = $this->createMock(Request::class);

        // Modifier A returns NONE
        $mod_none = $this->createMock(ScopeModifier::class);
        $mod_none->expects($this->once())->method('scope')->willReturn(Scope::NONE);

        // Modifier B should NOT be called
        $mod_ignored = $this->createMock(ScopeModifier::class);
        $mod_ignored->expects($this->never())->method('scope');

        Cache::$instance = new Cache(
            scope_modifiers: [
                'a_first'  => $mod_none,
                'b_second' => $mod_ignored,
            ],
        );

        $this->assertSame(Scope::NONE, Cache::scope($request));
    }

    public function test_modifiers_are_sorted_on_instantiation(): void
    {
        /*
         * Verify that passing unsorted arrays to __construct results in 
         * sorted properties in the instance.
         */
        $mod_a = $this->createMock(ScopeModifier::class);
        $mod_z = $this->createMock(ScopeModifier::class);

        $cache = new Cache(
            scope_modifiers: [
                'z_last'  => $mod_z,
                'a_first' => $mod_a,
            ],
        );

        // Check the keys of the property directly
        $keys = array_keys($cache->scope_modifiers);
        $this->assertSame(['a_first', 'z_last'], $keys, 'Modifiers should be ksorted in constructor');
    }

    public function test_scope_execution_follows_sorted_order(): void
    {
        $request = $this->createMock(Request::class);

        $mod_first = $this->createMock(ScopeModifier::class);
        $mod_first->expects($this->once())->method('scope')->willReturn(Scope::NONE);

        $mod_last = $this->createMock(ScopeModifier::class);
        $mod_last->expects($this->never())->method('scope');

        // Pass in WRONG order ('z' before 'a')
        // The constructor should fix this to 'a' before 'z'
        Cache::$instance = new Cache(
            scope_modifiers: [
                'z_last'  => $mod_last,
                'a_first' => $mod_first,
            ],
        );

        Cache::scope($request);
    }

    // -- Key Tests --

    public function test_key_concatenates_and_hashes_modifiers(): void
    {
        $request = $this->createMock(Request::class);

        $mod1 = $this->createMock(KeyModifier::class);
        $mod1->method('key')->willReturn('part1');

        $mod2 = $this->createMock(KeyModifier::class);
        $mod2->method('key')->willReturn('part2');

        Cache::$instance = new Cache(
            scope_modifiers: [],
            key_modifiers: [
                'a' => $mod1,
                'b' => $mod2,
            ],
        );

        $expected_hash = hash('sha256', 'part1|part2');
        $this->assertSame($expected_hash, Cache::key($request));
    }

    public function test_key_modifiers_are_sorted_before_hashing(): void
    {
        $request = $this->createMock(Request::class);

        $mod_z = $this->createMock(KeyModifier::class);
        $mod_z->method('key')->willReturn('Z');

        $mod_a = $this->createMock(KeyModifier::class);
        $mod_a->method('key')->willReturn('A');

        // Pass Z first. Constructor should sort A first.
        // Result should be hash(A|Z), NOT hash(Z|A).
        Cache::$instance = new Cache(
            scope_modifiers: [],
            key_modifiers: [
                'z' => $mod_z,
                'a' => $mod_a,
            ],
        );

        $expected_hash = hash('sha256', 'A|Z');
        $this->assertSame($expected_hash, Cache::key($request));
    }

    // -- Singleton Tests --

    public function test_instance_lazy_loads_defaults(): void
    {
        // Ensure no instance exists initially (handled by tearDown usually, but explicit here)
        if (isset(Cache::$instance))
            unset(Cache::$instance);

        $instance = Cache::instance();

        $this->assertInstanceOf(Cache::class, $instance);

        // Verify default modifiers are present
        $this->assertArrayHasKey('session', $instance->scope_modifiers);
        $this->assertArrayHasKey('cookies', $instance->scope_modifiers);
    }

    public function test_instance_returns_existing_singleton(): void
    {
        $existing        = new Cache();
        Cache::$instance = $existing;

        $this->assertSame($existing, Cache::instance());
    }

}
