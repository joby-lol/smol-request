<?php

/**
 * smolRequest
 * https://github.com/joby-lol/smol-request
 * (c) 2025 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Request\Headers;

readonly class RangeHeader extends AbstractListHeader
{
    /** @var array<RangeHeaderRange|RangeHeaderSuffix> $ranges */
    public array $ranges;

    public function __construct(string ...$items)
    {
        $ranges = array_map(static::parseItem(...), $items);
        $ranges = array_unique($ranges, SORT_REGULAR);
        usort(
            $ranges,
            function (RangeHeaderRange|RangeHeaderSuffix $a, $b): int {
                // sort suffix ranges last
                if ($a instanceof RangeHeaderSuffix && $b instanceof RangeHeaderRange) {
                    return 1;
                } elseif ($a instanceof RangeHeaderRange && $b instanceof RangeHeaderSuffix) {
                    return -1;
                }
                // if both are suffix ranges, sort by length descending
                if ($a instanceof RangeHeaderSuffix) {
                    return $b->end_bytes <=> $a->end_bytes;
                }
                // both are normal ranges, sort by start ascending then end ascending
                assert($b instanceof RangeHeaderRange); // phpstan can't figure this out I guess
                return $a->start_byte <=> $b->start_byte
                    ?: ($a->end_byte <=> $b->end_byte);
            },
        );
        $this->ranges = $ranges;
    }

    public static function parse(string $value): ?static
    {
        $value = trim($value);
        if ($value === "") {
            return null;
        }
        if (!str_starts_with($value, 'bytes=')) {
            throw new HeaderException("Invalid Range header, must start with 'bytes='. Got {$value}");
        }
        $value = substr($value, 6);
        return parent::parse($value);
    }

    protected static function parseItem(string $item): RangeHeaderRange|RangeHeaderSuffix
    {
        $parts = explode('-', $item);
        if (count($parts) !== 2) {
            throw new HeaderException("Invalid Range header, invalid range format: {$item}");
        }
        $start = trim($parts[0]);
        $end = trim($parts[1]);
        if ($start === '') {
            // Suffix range
            $end = (int) $end;
            if ($end != $parts[1]) {
                throw new HeaderException("Invalid Range header, invalid suffix range format: {$item}");
            }
            return new RangeHeaderSuffix($end);
        } else {
            // Normal range
            $end = $end === '' ? null : (int) $end;
            $start = (int) $start;
            if ($start != $parts[0] || ($end !== null && $end != $parts[1])) {
                throw new HeaderException("Invalid Range header, invalid range format: {$item}");
            }
            return new RangeHeaderRange(
                $start,
                $end,
            );
        }
    }

    public function __toString(): string
    {
        $parts = [];
        foreach ($this->ranges as $range) {
            $parts[] = (string) $range;
        }
        return 'bytes=' . implode(', ', $parts);
    }
}
