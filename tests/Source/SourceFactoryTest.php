<?php

/**
 * smolRequest
 * https://github.com/joby-lol/smol-request
 * (c) 2025 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Request\Source;

use Joby\Smol\Request\Source\SourceFactory;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class SourceFactoryTest extends TestCase
{

    private array $server_backup;

    protected function setUp(): void
    {
        $this->server_backup = $_SERVER;
    }

    protected function tearDown(): void
    {
        $_SERVER = $this->server_backup;
    }

    // -- IPv4 CIDR Logic Tests --

    #[DataProvider('ipv4_cidr_provider')]

    // <--- Use Attribute syntax
    public function test_ipv4_cidr_matching(string $ip, string $cidr, bool $expected): void
    {
        $factory = new SourceFactory([$cidr]);

        $this->assertSame(
            $expected,
            $factory->isTrustedProxy($ip),
            "Failed asserting that IP $ip matches CIDR $cidr",
        );
    }

    public static function ipv4_cidr_provider(): array
    {
        return [
            ['192.168.1.5', '192.168.1.0/24', true],
            ['192.168.1.0', '192.168.1.0/24', true],
            ['192.168.1.255', '192.168.1.0/24', true],
            ['192.168.2.1', '192.168.1.0/24', false],
            ['10.0.0.1', '10.0.0.1/32', true],
            ['10.0.0.2', '10.0.0.1/32', false],
            ['10.55.66.77', '10.0.0.0/8', true],
            ['11.0.0.1', '10.0.0.0/8', false],
            ['127.0.0.1', '0.0.0.0/0', true],
            ['192.168.1.127', '192.168.1.0/25', true],
            ['192.168.1.128', '192.168.1.0/25', false],
            ['invalid-ip', '192.168.1.0/24', false],
            ['192.168.1.5', 'invalid-cidr', false],
        ];
    }

    // -- IPv6 CIDR Logic Tests --

    #[DataProvider('ipv6_cidr_provider')]

    // <--- Attribute syntax here too
    public function test_ipv6_cidr_matching(string $ip, string $cidr, bool $expected): void
    {
        $factory = new SourceFactory([$cidr]);

        $this->assertSame(
            $expected,
            $factory->isTrustedProxy($ip),
            "Failed asserting that IPv6 $ip matches CIDR $cidr",
        );
    }

    public static function ipv6_cidr_provider(): array
    {
        return [
            ['2001:db8::1', '2001:db8::/64', true],
            ['2001:db8::ffff:ffff', '2001:db8::/64', true],
            ['2001:db8:1::1', '2001:db8::/64', false],
            ['fe80::1', 'fe80::1/128', true],
            ['fe80::2', 'fe80::1/128', false],
            ['2001:0db8:0000:0000:0000:0000:0000:0001', '2001:db8::/64', true],
            // Edge Cases
            ['::0', '::0/127', true],
            ['::1', '::0/127', true],
            ['::2', '::0/127', false],
            ['2001:db8::1', '2000::/12', true],
            ['200f:ffff::1', '2000::/12', true],
            ['2010::1', '2000::/12', false],
        ];
    }

    // ... Rest of the tests (Trusted Proxy Logic, Header Parsing, etc.) remain the same

    public function test_always_trusts_localhost(): void
    {
        $factory = new SourceFactory([]);
        $this->assertTrue($factory->isTrustedProxy('localhost'));
        $this->assertTrue($factory->isTrustedProxy('127.0.0.1'));
        $this->assertTrue($factory->isTrustedProxy('::1'));
    }

    public function test_wildcard_proxy_trusts_everything(): void
    {
        $factory = new SourceFactory(['*']);
        $this->assertTrue($factory->isTrustedProxy('10.0.0.5'));
    }

    public function test_exact_ip_match(): void
    {
        $factory = new SourceFactory(['1.2.3.4']);
        $this->assertTrue($factory->isTrustedProxy('1.2.3.4'));
        $this->assertFalse($factory->isTrustedProxy('1.2.3.5'));
    }

    public function test_client_from_headers_returns_null_if_headers_missing(): void
    {
        $_SERVER = [];
        $factory = new SourceFactory();
        $this->assertNull($factory->clientFromHeaders());
    }

    public function test_client_from_headers_respects_priority_order(): void
    {
        $factory                        = new SourceFactory([], ['x-priority-one', 'x-priority-two']);
        $_SERVER['HTTP_X_PRIORITY_ONE'] = '1.1.1.1';
        $_SERVER['HTTP_X_PRIORITY_TWO'] = '2.2.2.2';
        $this->assertSame('1.1.1.1', $factory->clientFromHeaders());
    }

    public function test_extracts_first_ip_from_comma_separated_list(): void
    {
        $factory                         = new SourceFactory([], ['x-forwarded-for']);
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '203.0.113.195, 70.41.3.18';
        $this->assertSame('203.0.113.195', $factory->clientFromHeaders());
    }

    public function test_trims_and_normalizes_header_ip(): void
    {
        $factory                         = new SourceFactory([], ['x-forwarded-for']);
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '  ::1  , 1.2.3.4';
        $this->assertSame('localhost', $factory->clientFromHeaders());
    }

    public function test_from_globals_handles_trusted_proxy_but_missing_headers(): void
    {
        $factory                = new SourceFactory(['10.0.0.1']);
        $_SERVER['REMOTE_ADDR'] = '10.0.0.1';
        $source                 = $factory->fromGlobals();
        $this->assertSame('10.0.0.1', $source->client);
    }

    public function test_constructor_normalizes_inputs(): void
    {
        $factory = new SourceFactory(
            ['10.0.0.1', '10.0.0.1', '127.0.0.1'],
            ['X-My-Header', 'x-my-header'],
        );
        $this->assertContains('10.0.0.1', $factory->trusted_proxies);
        $this->assertContains('localhost', $factory->trusted_proxies);
        $this->assertCount(2, $factory->trusted_proxies);
        $this->assertContains('x-my-header', $factory->trusted_headers);
        $this->assertCount(1, $factory->trusted_headers);
    }

    public function test_predefined_constants_work_as_config(): void
    {
        $factory = new SourceFactory(SourceFactory::TRUSTED_IPS_CLOUDFLARE);
        $this->assertTrue($factory->isTrustedProxy('103.21.244.5'));
    }

}
