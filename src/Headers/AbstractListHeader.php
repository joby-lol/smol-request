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
abstract readonly class AbstractListHeader implements HeaderInterface
{
    /** @var non-empty-string DELIMITER the delimiter used to separate list items in the header */
    public const string DELIMITER = ',';

    abstract public function __construct(string ...$items);

    public static function parse(string $value): ?static
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }
        $items = explode(static::DELIMITER, $value);
        $items = array_map(trim(...), $items);
        $items = array_unique($items);
        return new static(...$items);
    }
}
