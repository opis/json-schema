Opis JSON Schema
====================
[![Build Status](https://travis-ci.org/opis/json-schema.png)](https://travis-ci.org/opis/json-schema)
[![Latest Stable Version](https://poser.pugx.org/opis/json-schema/v/stable.png)](https://packagist.org/packages/opis/json-schema)
[![Latest Unstable Version](https://poser.pugx.org/opis/json-schema/v/unstable.png)](https://packagist.org/packages/opis/json-schema)
[![License](https://poser.pugx.org/opis/json-schema/license.png)](https://packagist.org/packages/opis/json-schema)

Validate JSON documents
-----------

**Opis JSON Schema** is a PHP implementation for the [JSON Schema] standard (draft-07 and draft-06), that
will help you validate all sorts of JSON documents, whether they are configuration files or a set 
of data sent to an RESTful API endpoint.


**The library's key features:**

- Fast validation (you can set maximum number of errors for a validation)
- Custom schema document [loaders](https://docs.opis.io/json-schema/1.x/php-loader.html)
- Support for [if-then-else](https://docs.opis.io/json-schema/1.x/conditional-subschemas.html#if-then-else)
- All [string formats](https://docs.opis.io/json-schema/1.x/formats.html#provided-formats) are supported
- Support for custom [formats](https://docs.opis.io/json-schema/1.x/php-format.html)
- Support for custom [media types](https://docs.opis.io/json-schema/1.x/php-media-type.html)
- Support for [default value](https://docs.opis.io/json-schema/1.x/default-value.html)
- Support for custom variables using [`$vars` keyword](https://docs.opis.io/json-schema/1.x/variables.html)
- Support for custom filters using [`$filters` keyword](https://docs.opis.io/json-schema/1.x/filters.html)
- Advanced schema reuse using [`$map` keyword](https://docs.opis.io/json-schema/1.x/mappers.html)
- Support for [json pointers](https://docs.opis.io/json-schema/1.x/pointers.html) (absolute and relative pointers)
- Support for [URI templates](https://docs.opis.io/json-schema/1.x/uri-template.html)

### Documentation

The full documentation for this library can be found [here][documentation].
We provide documentation for both [JSON Schema] standard itself as well as for
the library's own API. 

### License

**Opis JSON Schema** is licensed under the [Apache License, Version 2.0][apache_license].

### Requirements

* PHP ^7.0

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
        "opis/json-schema": "^1.0"
    }
}
```

[documentation]: https://docs.opis.io/json-schema
[apache_license]: https://www.apache.org/licenses/LICENSE-2.0 "Apache License"
[Packagist]: https://packagist.org/packages/opis/json-schema "Packagist"
[Composer]: https://getcomposer.org "Composer"
[JSON Schema]: http://json-schema.org/ "JSON Schema"
