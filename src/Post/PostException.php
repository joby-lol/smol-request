<?php

/**
 * smolRequest
 * https://github.com/joby-lol/smol-request
 * (c) 2025 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Request\Post;

use Joby\Smol\Request\RequestException;

/**
 * Exception for Post-related errors. Indicates that something was wrong with POST data, and should generally lead to the client getting an HTTP 400 response to indicate that the request was invalid and should not be retried.
 */
class PostException extends RequestException
{
}
