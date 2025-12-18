<?php

/**
 * smolRequest
 * https://github.com/joby-lol/smol-request
 * (c) 2025 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Request\Post;

/**
 * Wrapper for storing the bare minimum information about an uploaded file.
 */
readonly class PostFile
{
    public function __construct(
        public string $filename,
        public string $tmp_name,
        public int $size,
    ) {
    }
}
