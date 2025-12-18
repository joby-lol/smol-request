<?php

/**
 * smolRequest
 * https://github.com/joby-lol/smol-request
 * (c) 2025 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Request\Headers;

readonly class AcceptLanguageHeader extends AbstractQualityListHeader
{
    /** @var array<string> $languages */
    public array $languages;

    public function __construct(string ...$items)
    {
        $this->languages = array_unique(array_map(strtolower(...), $items));
    }

    /**
     * Choose the best matching language from the provided list based on the Accept-Language header.
     */
    public function chooseBest(string ...$available_languages): ?string
    {
        $available_languages = array_unique(array_map(strtolower(...), $available_languages));
        // iterate preferred languages from header in order, return first match -- first look for exact matches
        foreach ($this->languages as $preferred) {
            foreach ($available_languages as $available) {
                if ($preferred === $available) {
                    return $available;
                }
            }
        }
        // then look for primary tag matches
        foreach ($this->languages as $preferred) {
            $preferred_primary = explode('-', $preferred, 2)[0];
            foreach ($available_languages as $available) {
                $available_primary = explode('-', $available, 2)[0];
                if ($preferred_primary === $available_primary) {
                    return $available;
                }
            }
        }
        return null;
    }

    public function __toString(): string
    {
        return implode(', ', $this->languages);
    }
}
