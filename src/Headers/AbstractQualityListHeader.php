<?php

/**
 * smolRequest
 * https://github.com/joby-lol/smol-request
 * (c) 2025 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Request\Headers;

/**
 * @phpstan-consistent-constructor
 */
abstract readonly class AbstractQualityListHeader implements HeaderInterface
{
    /** @var non-empty-string DELIMITER the delimiter used to separate list items in the header */
    public const string DELIMITER = ',';

    abstract public function __construct(string ...$items);

    public static function parse(string $value): ?static
    {
        $items = array_map(trim(...), explode(static::DELIMITER, $value));
        // convert items into array of value and quality value pairs for sorting
        $items = array_map(
            /** @return array{value: string, quality: float<0,1>} */
            function (string $item): array {
                $params = array_map(trim(...), explode(';', $item));
                $value = array_shift($params);
                $quality = 1.0;
                $otherParams = [];
                foreach ($params as $param) {
                    if (str_starts_with(strtolower($param), 'q=')) {
                        $quality = (float) substr($param, 2);
                    } else {
                        $otherParams[] = $param;
                    }
                }
                // check quality value is valid
                if ($quality < 0.0 || $quality > 1.0) {
                    throw new HeaderException("Quality value must be between 0 and 1, got: $quality");
                }
                // reassemble value if there are other parameters
                if (count($otherParams) > 0) {
                    $value .= ';' . implode(';', $otherParams);
                }
                // return value/quality pair
                return ['value' => $value, 'quality' => $quality];
            },
            $items,
        );
        // sort by quality value descending
        usort(
            $items,
            function ($a, $b): int {
                return $b['quality'] <=> $a['quality']
                    ?: static::valueSort($a['value'], $b['value']);
            },
        );
        // extract just the unique values and return
        $items = array_map(fn($item) => $item['value'], $items);
        $items = array_unique($items);
        return new static(...$items);
    }

    /**
     * Compare two header values for sorting purposes, in the case where quality values are tied. Default implementation is string comparison, but this can be overridden in subclasses.
     */
    protected static function valueSort(string $a, string $b): int
    {
        return $a <=> $b;
    }
}
