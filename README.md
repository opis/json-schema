Opis JSON Schema
====================
[![Tests](https://github.com/opis/json-schema/workflows/Tests/badge.svg)](https://github.com/opis/json-schema/actions)
[![Packagist Version](https://img.shields.io/packagist/v/opis/json-schema?label=Version)](https://packagist.org/packages/opis/json-schema)
[![Packagist Downloads](https://img.shields.io/packagist/dt/opis/json-schema?label=Downloads)](https://packagist.org/packages/opis/json-schema)
[![Packagist License](https://img.shields.io/packagist/l/opis/json-schema?color=teal&label=License)](https://packagist.org/packages/opis/json-schema)

Validate JSON documents
-----------

**Opis JSON Schema** is a PHP implementation for the [JSON Schema] standard (draft-2020-12, draft-2019-09, draft-07 and draft-06), that
will help you validate all sorts of JSON documents, whether they are configuration files or a set 
of data sent to a RESTful API endpoint.


**The library's key features:**

- Supports all keywords from all drafts (draft-2020-12 down to draft-06)
- Support for custom errors inside schema using [`$error` keyword](https://opis.io/json-schema/2.x/errors.html)
- Support for custom PHP filters using [`$filters` keyword](https://docs.opis.io/json-schema/2.x/filters.html)
- Advanced schema reuse using [`$map` keyword](https://docs.opis.io/json-schema/2.x/mappers.html)
- Intuitive schema composition using [slots](https://docs.opis.io/json-schema/2.x/slots.html)
- Support for absolute & relative [json pointers](https://docs.opis.io/json-schema/2.x/pointers.html)
- Support for [URI templates](https://docs.opis.io/json-schema/2.x/uri-template.html)
- Support for [`$data` keyword](https://docs.opis.io/json-schema/2.x/data-keyword.html)
- Support for [casting](https://docs.opis.io/json-schema/2.x/pragma.html#cast)
- Support for custom [formats](https://docs.opis.io/json-schema/2.x/php-format.html) and [media types](https://docs.opis.io/json-schema/2.x/php-media-type.html)

### Documentation

The full documentation for this library can be found [here][documentation].
We provide documentation for both [JSON Schema] standard itself as well as for
the library's own API. 

### License

**Opis JSON Schema** is licensed under the [Apache License, Version 2.0][apache_license].

### Requirements

* PHP ^7.4 || ^8.0

## Installation

**Opis JSON Schema** is available on [Packagist] and it can be installed from a 
command line interface by using [Composer]. 

```bash
composer require opis/json-schema
```

Or you could directly reference it into your `composer.json` file as a dependency

```json
{
    "require": {
        "opis/json-schema": "^2.4"
    }
}
```

[documentation]: https://opis.io/json-schema
[apache_license]: https://www.apache.org/licenses/LICENSE-2.0 "Apache License"
[Packagist]: https://packagist.org/packages/opis/json-schema "Packagist"
[Composer]: https://getcomposer.org "Composer"
[JSON Schema]: http://json-schema.org/ "JSON Schema"
