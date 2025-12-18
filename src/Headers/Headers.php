<?php

/**
 * smolRequest
 * https://github.com/joby-lol/smol-request
 * (c) 2025 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Request\Headers;

readonly class Headers
{

    /** @var array<string,GenericHeader> $generic */
    public array $generic;

    /**
     * @param array<string,GenericHeader> $generic
     */
    public function __construct(
        public ?AcceptHeader $accept,
        public ?RangeHeader $range,
        public ?IfModifiedSinceHeader $if_modified_since,
        public ?IfNoneMatchHeader $if_none_match,
        array $generic,
    ) {
        $built_generic = [];
        foreach ($generic as $key => $value) {
            $built_generic[strtolower($key)] = $value;
        }
        ksort($built_generic);
        $this->generic = $built_generic;
    }

    public function has(string $header): bool
    {
        $header = strtolower($header);
        return match ($header) {
            'accept'            => $this->accept !== null,
            'range'             => $this->range !== null,
            'if-modified-since' => $this->if_modified_since !== null,
            'if-none-match'     => $this->if_none_match !== null,
            default             => array_key_exists($header, $this->generic),
        };
    }

    public function get(string $header): ?HeaderInterface
    {
        $header = strtolower($header);
        return match ($header) {
            'accept'            => $this->accept,
            'range'             => $this->range,
            'if-modified-since' => $this->if_modified_since,
            'if-none-match'     => $this->if_none_match,
            default             => $this->generic[$header] ?? null,
        };
    }

}
