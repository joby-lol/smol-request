<?php

/**
 * smolRequest
 * https://github.com/joby-lol/smol-request
 * (c) 2025 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Request\Headers;

class HeadersFactory
{

    /**
     * Create a Headers object from an associative array of header names and values, such as what is returned by getallheaders().
     *
     * @param array<string, string> $headers
     */
    public function fromArray(array $headers): Headers
    {
        // TODO: some way of handling more generic parsing so that we can have custom classes, without having to edit this method each time, then maybe a get() method to retrieve by class? That keeps type safety real tidy.
        $generic_headers = [];
        foreach ($headers as $name => $value) {
            $generic_headers[strtolower($name)] = $value;
        }
        $accept = AcceptHeader::parse($generic_headers['accept'] ?? '');
        unset($generic_headers['accept']);
        $range = RangeHeader::parse($generic_headers['range'] ?? '');
        unset($generic_headers['range']);
        $if_modified_since = IfModifiedSinceHeader::parse($generic_headers['if-modified-since'] ?? '');
        unset($generic_headers['if-modified-since']);
        $if_none_match = IfNoneMatchHeader::parse($generic_headers['if-none-match'] ?? '');
        unset($generic_headers['if-none-match']);
        $generic_headers = array_map(GenericHeader::parse(...), $generic_headers);
        return new Headers(
            $accept,
            $range,
            $if_modified_since,
            $if_none_match,
            $generic_headers,
        );
    }

    public function fromGlobals(): Headers
    {
        return $this->fromArray($this->extractHeadersFromServer());
    }

    /**
     * Extract headers from the $_SERVER superglobal.
     *
     * @return array<string, string>
     */
    protected function extractHeadersFromServer(): array
    {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (!is_scalar($value)) {
                continue;
            }
            $value = strval($value);
            if (str_starts_with($key, 'HTTP_')) {
                $header_name = str_replace(' ', '-', strtolower(str_replace('_', '-', substr($key, 5))));
                $headers[$header_name] = $value;
            }
            elseif (in_array($key, ['CONTENT_TYPE', 'CONTENT_LENGTH', 'CONTENT_MD5'])) {
                $header_name = str_replace(' ', '-', strtolower(str_replace('_', '-', $key)));
                $headers[$header_name] = $value;
            }
        }
        return $headers;
    }

}
