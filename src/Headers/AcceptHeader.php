<?php

/**
 * smolRequest
 * https://github.com/joby-lol/smol-request
 * (c) 2025 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Request\Headers;

readonly class AcceptHeader extends AbstractQualityListHeader
{
    /** @var AcceptHeaderMediaType[] $types */
    public array $types;

    public function __construct(string ...$items)
    {
        $types = array_map(AcceptHeaderMediaType::parse(...), $items);
        $types = array_filter($types);
        $types = array_unique($types, SORT_REGULAR);
        $types = array_values($types);
        $this->types = $types;
    }

    /**
     * AcceptHeader overrides valueSort to ensure that when quality values are tied, more specific media types are prioritized (e.g. text/html over text/*). Barring that, it falls back to counting additional parameters, and finally to string comparison.
     */
    protected static function valueSort(string $a, string $b): int
    {
        $a_original = $a;
        $b_original = $b;
        $a = explode(';', $a);
        $b = explode(';', $b);
        $a[0] = array_pad(explode('/', $a[0]), 2, '');
        $b[0] = array_pad(explode('/', $b[0]), 2, '');
        // first compare specificity of type/subtype
        if ($a[0][0] === '*' && $b[0][0] !== '*') {
            return 1;
        } elseif ($a[0][0] !== '*' && $b[0][0] === '*') {
            return -1;
        }
        if ($a[0][1] === '*' && $b[0][1] !== '*') {
            return 1;
        } elseif ($a[0][1] !== '*' && $b[0][1] === '*') {
            return -1;
        }
        // then compare number of parameters (more parameters = more specific)
        if (count($a) !== count($b)) {
            return count($b) <=> count($a);
        }
        // finally fall back to string comparison
        return $a_original <=> $b_original;
    }

    public function __toString(): string
    {
        $parts = [];
        foreach ($this->types as $type) {
            $parts[] = (string) $type;
        }
        return implode(', ', $parts);
    }
}
