# smolRequest

A straightforward modern abstraction layer for retrieving information about and data from the current HTTP request. Designed to minimize footguns, maximize productivity, and provide useful hinting and intellisense features in your IDE.

## Installation

```bash
composer require joby/smol-request
```

## Usage examples

All data about the current request can be accessed through `Request::current()`, and should be ready to go out of the box with no configuration on most servers.

```php
use Joby\Smol\Request\Request;

// All parts of the request are accessible via properties on the current object

// a type-safe representation of the request URL
Request::current()->url;
// a type-safe enum representation of the request method
Request::current()->method;
// a type-safe representation of the request headers
Request::current()->headers;
// a type-safe representation of the request cookies
Request::current()->cookies;
// a type-safe representation of the request post data
Request::current()->post;
// a type-safe representation of the requesting IP address
Request::current()->source;
```