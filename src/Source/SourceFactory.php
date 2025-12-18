<?php

/**
 * smolRequest
 * https://github.com/joby-lol/smol-request
 * (c) 2025 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Request\Source;

readonly class SourceFactory
{
    /** @var array<string> $trusted_proxies */
    public array $trusted_proxies;
    /** @var array<string> $trusted_headers */
    public array $trusted_headers;

    public const TRUSTED_IPS_CLOUDFLARE = [
        '103.21.244.0/22',
        '103.22.200.0/22',
        '103.31.4.0/22',
        '104.16.0.0/13',
        '104.24.0.0/14',
        '108.162.192.0/18',
        '131.0.72.0/22',
        '141.101.64.0/18',
        '162.158.0.0/15',
        '172.64.0.0/13',
        '173.245.48.0/20',
        '188.114.96.0/20',
        '190.93.240.0/20',
        '197.234.240.0/22',
        '198.41.128.0/17',
        '2400:cb00::/32',
        '2606:4700::/32',
        '2803:f800::/32',
        '2405:b500::/32',
        '2405:8100::/32',
        '2a06:98c0::/29',
        '2c0f:f248::/32',
    ];

    public const TRUSTED_IPS_FASTLY = [
        '23.235.32.0/20',
        '43.249.72.0/22',
        '103.244.50.0/24',
        '103.245.222.0/23',
        '103.245.224.0/24',
        '104.156.80.0/20',
        '140.248.64.0/18',
        '140.248.128.0/17',
        '146.75.0.0/17',
        '151.101.0.0/16',
        '157.52.64.0/18',
        '167.82.0.0/17',
        '167.82.128.0/20',
        '167.82.160.0/20',
        '167.82.224.0/20',
        '172.111.64.0/18',
        '185.31.16.0/22',
        '199.27.72.0/21',
        '199.232.0.0/16',
        '2a04:4e40::/32',
        '2a04:4e42::/32',
    ];

    /**
     * @param array<string> $trusted_proxies
     * @param array<string> $trusted_headers
     */
    public function __construct(
        array $trusted_proxies = [],
        array $trusted_headers = [
            'cf-connecting-ip', // Cloudflare
            'x-real-ip', // Nginx and FastCGI
            'x-forwarded-for', // Standard (ish)
            'forwarded', // RFC 7239
        ],
    ) {
        $this->trusted_proxies = array_unique(array_map($this->normalize(...), $trusted_proxies));
        $this->trusted_headers = array_unique(array_map(strtolower(...), $trusted_headers));
    }

    /**
     * @codeCoverageIgnore this is just a wrapper, really
     */
    public function fromGlobals(): Source
    {
        // actual IP is easy
        // @phpstan-ignore-next-line we're trusting that $_SERVER['REMOTE_ADDR'] is a string if set, and if it isn't that's a good time to error out
        $client = $actual = $this->normalize(@$_SERVER['REMOTE_ADDR'] ?? 'localhost');
        // check if we trust this actual IP
        if ($this->isTrustedProxy($actual)) {
            $client = $this->clientFromHeaders() ?? $actual;
        }
        return new Source($client, $actual);
    }

    public function clientFromHeaders(): ?string
    {
        foreach ($this->trusted_headers as $header) {
            $header_key = 'HTTP_' . strtoupper(str_replace('-', '_', $header));
            if (isset($_SERVER[$header_key])) {
                /** @var string $header_value */
                $header_value = $_SERVER[$header_key];
                // handle multiple IPs in a header (like X-Forwarded-For)
                // this takes the first IP only, because that's the original client IP
                $ips = explode(',', $header_value);
                $ip = $this->normalize(trim($ips[0]));
                return $ip;
            }
        }
        return null;
    }

    public function isTrustedProxy(string $ip): bool
    {
        // normalize
        $ip = $this->normalize($ip);
        // always trust localhost
        if ($ip === 'localhost') {
            return true;
        }
        // try trusted proxies
        foreach ($this->trusted_proxies as $trusted) {
            // allow wildcard proxies, for stuff like dev environments
            if ($trusted === '*') {
                return true;
            }
            // check for an exact match
            if ($this->normalize($trusted) === $ip) {
                return true;
            }
            // CIDR match
            if (str_contains($trusted, '/')) {
                if ($this->isCidrMatch($ip, $trusted)) {
                    return true;
                }
            }
        }
        return false;
    }

    protected function isCidrMatch(string $ip, string $cidr): bool
    {
        // verify that IP is a valid IP
        if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
            return false;
        }
        $c = explode('/', $cidr);
        $subnet = $c[0];
        if (empty($subnet)) {
            return false;
        }
        $mask = isset($c[1]) ? (int) $c[1] : null;
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $mask = $mask ?? 32;
            return $this->isCidrMatchV4($ip, $subnet, $mask);
        } elseif (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $mask = $mask ?? 128;
            return $this->isCidrMatchV6($ip, $subnet, $mask);
        } else {
            return false;
        }
    }

    protected function isCidrMatchV4(string $ip, string $subnet, int $mask): bool
    {
        if ($mask < 0 || $mask > 32) {
            return false;
        }
        $ip_long = ip2long($ip);
        $subnet_long = ip2long($subnet);
        $mask_long = -1 << (32 - $mask);
        $subnet_long &= $mask_long;
        return ($ip_long & $mask_long) === $subnet_long;
    }

    protected function isCidrMatchV6(string $ip, string $subnet, int $mask): bool
    {
        if ($mask < 0 || $mask > 128) {
            return false;
        }
        $ip_bin = inet_pton($ip);
        $subnet_bin = inet_pton($subnet);
        if ($ip_bin === false || $subnet_bin === false) {
            return false;
        }
        $bytes = (int) floor($mask / 8);
        $bits = $mask % 8;
        for ($i = 0; $i < $bytes; $i++) {
            if ($ip_bin[$i] !== $subnet_bin[$i]) {
                return false;
            }
        }
        if ($bits > 0) {
            $mask_byte = (0xFF00 >> $bits) & 0xFF;
            if ((ord($ip_bin[$bytes]) & $mask_byte) !== (ord($subnet_bin[$bytes]) & $mask_byte)) {
                return false;
            }
        }
        return true;
    }

    protected function normalize(string $input): string
    {
        $input = trim(strtolower($input));
        if ($input === '127.0.0.1') {
            return 'localhost';
        }
        if ($input === '::1') {
            return 'localhost';
        }
        return $input;
    }
}
