# smolRequest

A straightforward, type-safe abstraction layer for retrieving information about and data from the current HTTP request. Designed to minimize footguns, maximize productivity, and provide useful hinting and IntelliSense features in your IDE.

**Features:**
- Type-safe request data access with PHP enums and strongly-typed classes
- Built-in HTTP method enum (GET, HEAD, POST, PUT, DELETE, PATCH)
- Structured parsing of complex headers (Accept, Range, If-Modified-Since, If-None-Match, etc.)
- Type-safe cookie handling with validation
- POST data and file upload handling with built-in file type detection
- Request caching helpers with scope and key modifiers
- Requires PHP 8.3+

## Installation

```bash
composer require joby/smol-request
```

## Quick Start

All data about the current request can be accessed through `Request::current()`, and should be ready to go out of the box with no configuration on most servers.

```php
use Joby\Smol\Request\Request;

// Get the current request
$request = Request::current();

// Type-safe URL access (via smol-url library)
$url = $request->url;
$host = $url->host;
$path = $url->path;

// HTTP method as enum
if ($request->method === \Joby\Smol\Request\Method::POST) {
    // Handle POST request
}

// Type-safe headers
if ($request->headers->has('accept')) {
    $accept = $request->headers->accept; // AcceptHeader
}

// Type-safe cookies
$sessionId = $request->cookies->get('PHPSESSID');

// Type-safe POST data
$email = $request->post->get('email');
$upload = $request->post->file('upload');

// Client source information
$clientIp = $request->source->client;  // Client IP from proxy (configurable trust settings)
$actualIp = $request->source->actual;  // Actual IP making request
```

## Core Components

### Request (`Request`)
The main facade class providing access to all request data. Use `Request::current()` to get the current request.

**Properties:**
- `url`: URL object (from smolURL library)
- `method`: HTTP method enum
- `headers`: Structured header collection
- `cookies`: Cookie collection
- `post`: POST data and file uploads
- `source`: Client source information

### HTTP Method (`Method`)
An enum representing HTTP methods:
- `GET`
- `HEAD`
- `POST`
- `PUT`
- `DELETE`
- `PATCH`

### Headers (`Headers`)
Type-safe header handling with special parsing for common headers:

**Special Headers:**
- `accept` - Parsed `Accept` header with media types and quality values
- `range` - Parsed `Range` header with range specifications
- `if_modified_since` - Parsed `If-Modified-Since` header
- `if_none_match` - Parsed `If-None-Match` header (ETag)

**Generic Headers:**
All other headers are available in the `generic` array.

```php
// Check if header exists
if ($request->headers->has('x-custom-header')) {
    $header = $request->headers->get('x-custom-header');
}

// Access parsed headers
if ($request->headers->accept) {
    foreach ($request->headers->accept->media_types as $mediaType) {
        // $mediaType is AcceptHeaderMediaType
    }
}
```

### Cookies (`Cookies`)
Immutable cookie collection with automatic type normalization:

```php
// Get a cookie value
$value = $request->cookies->get('name');

// Check if cookie exists
if ($request->cookies->has('session_id')) {
    $sessionId = $request->cookies->cookies['session_id'];
}

// All cookies as array
$allCookies = $request->cookies->cookies; // array<string,string>
```

### POST Data (`Post`)
Structured access to POST form data and file uploads:

```php
// Get form field
$name = $request->post->get('name', 'default value');

// Get required field (throws PostException if missing)
$email = $request->post->require('email');

// Get typed values
$age = $request->post->getInt('age');
$price = $request->post->getFloat('price');
$active = $request->post->getBool('active');

// Access uploaded files
$files = $request->post->files['profile_picture'] ?? [];
foreach ($files as $file) {
    // $file is PostFile
}

// All form values
$args = $request->post->args; // array<string,string>
```

### Source (`Source`)
Information about the request source:

```php
// Client IP (after proxy forwarding if applicable)
$clientIp = $request->source->client;

// Direct connection IP
$actualIp = $request->source->actual;
```

## Advanced Usage

### Request Caching Helpers

Generate cache scopes and keys based on request characteristics:

```php
// Get cache scope (affected by authorization, cookies, headers, session)
$scope = $request->cacheScope();

// Get cache key
$cacheKey = $request->cacheKey();
```

### Creating Modified Requests

Create new request instances with modified properties:

```php
// Create a new request with different method
$modified = $request->with(
    method: \Joby\Smol\Request\Method::POST
);

// All parameters are optional - only specified properties are replaced
$modified = $request->with(
    url: $newUrl,
    headers: $newHeaders,
    cookies: $newCookies,
);
```

### Custom Request Factories

Customize how requests are built from global state:

```php
$factory = new \Joby\Smol\Request\RequestFactory();
// ... configure factory as needed

\Joby\Smol\Request\Request::setCurrentFactory($factory);
```

## Requirements

Fully tested on PHP 8.3+

## License

MIT License - See [LICENSE](LICENSE) file for details.