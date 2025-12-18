<?php

/**
 * smolRequest
 * https://github.com/joby-lol/smol-request
 * (c) 2025 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Request\Post;

use Throwable;

/**
 * Exception indicating that there was an issue with a file upload. Generally this should lead to the client getting an HTTP 400 response to indicate that the request was invalid and should not be retried.
 *
 * @codeCoverageIgnore
 */
class UploadException extends PostException
{
    /** @var array<int,string> ERROR_CODES */
    public const array ERROR_CODES = [
        UPLOAD_ERR_OK => 'No upload error',
        UPLOAD_ERR_CANT_WRITE => 'Cannot write to disk',
        UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload',
        UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the upload_max_filesize directive from the form',
        UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive from php.ini',
        UPLOAD_ERR_NO_FILE => 'No file was uploaded',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
        UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded',
    ];

    public function __construct(string $message, ?int $upload_err_code = null, ?Throwable $previous = null)
    {
        if (!is_null($upload_err_code)) {
            $error_message = static::ERROR_CODES[$upload_err_code] ?? 'Unknown error';
            $message .= ': ' . $error_message;
        }
        parent::__construct($message, 0, $previous);
    }
}
