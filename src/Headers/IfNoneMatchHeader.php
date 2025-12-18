<?php

/**
 * smolRequest
 * https://github.com/joby-lol/smol-request
 * (c) 2025 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Request\Headers;

readonly class IfNoneMatchHeader extends AbstractListHeader
{
    /** @var array<non-empty-string> $etags a sorted list of all provided etags */
    public array $etags;

    public function __construct(string ...$items)
    {
        $etags = [];
        foreach ($items as $item) {
            // Weak flag is ignored for If-None-Match
            if (str_starts_with($item, 'W/')) {
                $item = substr($item, 2);
            }
            // Trim quotes and whitespace and save
            $etags[] = trim($item, ' "');
        }
        // store filtered, unique, and sorted list
        $etags = array_filter($etags, fn ($etag) => $etag !== '');
        $etags = array_unique($etags);
        sort($etags);
        $this->etags = $etags;
    }

    /**
     * Returns true if none of the provided etags match any of those in the header.
     */
    public function noneMatch(string ...$etags): bool
    {
        foreach ($etags as $etag) {
            if (in_array($etag, $this->etags, true)) {
                return false;
            }
        }
        return true;
    }

    public function __toString(): string
    {
        $parts = [];
        foreach ($this->etags as $etag) {
            $parts[] = '"' . $etag . '"';
        }
        return implode(', ', $parts);
    }
}
