<?php

/**
 * smolRequest
 * https://github.com/joby-lol/smol-request
 * (c) 2025 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Request\Headers;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;

readonly class IfModifiedSinceHeader implements HeaderInterface
{
    public function __construct(
        public DateTimeImmutable $date,
    ) {
    }

    public static function parse(string $value): ?self
    {
        try {
            $date = new DateTimeImmutable($value, new DateTimeZone('GMT'));
            return new self($date);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function modifiedSince(DateTimeInterface|string|int $last_modified): bool
    {
        // normalize to a timestamp
        if (is_string($last_modified)) {
            try {
                $last_modified = new DateTimeImmutable($last_modified, new DateTimeZone('GMT'));
                $last_modified = $last_modified->getTimestamp();
            } catch (\Exception $e) {
                throw new \InvalidArgumentException("Invalid date string provided: $last_modified");
            }
        }
        if ($last_modified instanceof DateTimeInterface) {
            $last_modified = $last_modified->getTimestamp();
        }
        return $last_modified > $this->date->getTimestamp();
    }

    public function __toString(): string
    {
        return $this->date->format('D, d M Y H:i:s') . ' GMT';
    }
}
