<?php

/**
 * smolRequest
 * https://github.com/joby-lol/smol-request
 * (c) 2025 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Request\Headers;

use Stringable;

readonly class AcceptHeaderMediaType implements Stringable
{
    /**
     * @param non-empty-string $type
     * @param non-empty-string $subtype
     * @param array<non-empty-string,string|null> $parameters
     */
    public function __construct(
        public string $type,
        public string $subtype,
        public array $parameters = [],
    ) {
        if (!$type) {
            throw new \InvalidArgumentException('Type cannot be empty');
        }
        if (!$subtype) {
            throw new \InvalidArgumentException('Subtype cannot be empty');
        }
    }

    public static function parse(string $input): ?self
    {
        $parameters = explode(';', $input);
        $type = array_shift($parameters);
        $typeParts = explode('/', $type, 2);
        if (count($typeParts) !== 2) {
            return null;
        }
        $typeParts[0] = trim($typeParts[0]);
        $typeParts[1] = trim($typeParts[1]);
        if ($typeParts[0] === '' || $typeParts[1] === '') {
            return null;
        }
        // build parameters array
        $built_params = [];
        foreach ($parameters as $param) {
            $param = trim($param);
            if (!$param) {
                continue;
            }
            if (str_contains($param, '=')) {
                [$key, $value] = explode('=', $param, 2);
                $key = trim($key);
                $value = trim($value);
                if ($key === '' || $value === '') {
                    continue;
                }
                $built_params[strtolower($key)] = $value;
            } else {
                $built_params[strtolower($param)] = null;
            }
        }
        ksort($built_params);
        // return built object
        return new self(
            type: strtolower($typeParts[0]),
            subtype: strtolower($typeParts[1]),
            parameters: $built_params,
        );
    }

    public function __toString(): string
    {
        $parameters = '';
        foreach ($this->parameters as $key => $value) {
            $parameters .= '; ';
            if ($value === null) {
                $parameters .= $key;
            } else {
                $parameters .= sprintf('%s=%s', $key, $value);
            }
        }
        return sprintf(
            '%s/%s%s',
            $this->type,
            $this->subtype,
            $parameters,
        );
    }
}
