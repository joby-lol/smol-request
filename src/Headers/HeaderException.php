<?php

/**
 * smolRequest
 * https://github.com/joby-lol/smol-request
 * (c) 2025 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Request\Headers;

use Joby\Smol\Request\RequestException;

/**
 * Exception indicating that there was an issue parsing or handling a header. Generally this should lead to the client getting an HTTP 400 response to indicate that the request was invalid and should not be retried.
 */
class HeaderException extends RequestException
{
}
