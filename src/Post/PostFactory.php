<?php

/**
 * smolRequest
 * https://github.com/joby-lol/smol-request
 * (c) 2025 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Request\Post;

class PostFactory
{
    /**
     * @param array<string, mixed> $post
     * @param array<mixed> $files in the format of $_FILES (such as it is)
     */
    public function fromArrays(array $post, array $files): Post
    {
        return new Post(
            $post, // @phpstan-ignore-line we're trusting Post to validate this
            $this->normalizeFilesArray($files),
        );
    }

    /**
     * @codeCoverageIgnore
     */
    public function fromGlobals(): Post
    {
        // @phpstan-ignore-next-line this stuff is WAY more work than it's worth to type-hint
        return $this->fromArrays($_POST, $_FILES);
    }

    /**
     * @param array<mixed> $files in the format of $_FILES (such as it is)
     * @return array<string, array<int, PostFile>>
     */
    protected function normalizeFilesArray(array $files): array
    {
        $normalized = [];
        foreach ($files as $key => $file) {
            assert(is_string($key));
            // @phpstan-ignore-next-line $_FILES is stupid
            if (is_array($file['error'])) {
                // this key has multiple files uploaded through it
                foreach ($file['error'] as $i => $error) {
                    // check for deep nesting
                    if (is_array($error)) {
                        throw new UploadException("Deeply nested file uploads are not supported");
                    }
                    assert(is_int($i));
                    // check for errors
                    if ($error === UPLOAD_ERR_NO_FILE) {
                        // no file was uploaded for this index, skip it
                        continue;
                    }
                    if ($error !== UPLOAD_ERR_OK) {
                        // @phpstan-ignore-next-line $_FILES is stupid
                        throw new UploadException("File upload error for key '$key' in file number '$i'", $error);
                    }
                    // @phpstan-ignore-next-line $_FILES is stupid
                    $normalized[$key][] = new PostFile($file['name'][$i], $file['tmp_name'][$i], $file['size'][$i]);
                }
            } else {
                // check for errors
                if ($file['error'] === UPLOAD_ERR_NO_FILE) {
                    // no file was uploaded for this key, skip it
                    continue;
                }
                if ($file['error'] !== UPLOAD_ERR_OK) {
                    // @phpstan-ignore-next-line $_FILES is stupid
                    throw new UploadException("File upload error for key '$key'", $file['error']);
                }
                // this key has a single file uploaded through it
                // @phpstan-ignore-next-line $_FILES is stupid
                $normalized[$key][0] = new PostFile($file['name'], $file['tmp_name'], $file['size']);
            }
        }
        ksort($normalized);
        return $normalized;
    }
}
