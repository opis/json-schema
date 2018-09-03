Opis Json Schema
====================
[![Build Status](https://travis-ci.org/opis/json-schema.png)](https://travis-ci.org/opis/json-schema)
[![Latest Stable Version](https://poser.pugx.org/opis/json-schema/v/stable.png)](https://packagist.org/packages/opis/json-schema)
[![Latest Unstable Version](https://poser.pugx.org/opis/json-schema/v/unstable.png)](https://packagist.org/packages/opis/json-schema)
[![License](https://poser.pugx.org/opis/json-schema/license.png)](https://packagist.org/packages/opis/json-schema)

Json Schema
-----------

**Opis Json Schema** is a PHP implementation for [json-schema](http://json-schema.org/) draft-07 and draft-06.

**The library's key features:**

- Fast validation (you can set maximum number of errors for a validation)
- Custom schema document [loaders](https://www.opis.io/json-schema/1.x/php-loader.html)
- Support for [if-then-else](https://www.opis.io/json-schema/1.x/conditional-subschemas.html#if-then-else)
- All [string formats](https://www.opis.io/json-schema/1.x/formats.html#provided-formats) are supported
- Support for custom [formats](https://www.opis.io/json-schema/1.x/php-format.html)
- Support for custom [media types](https://www.opis.io/json-schema/1.x/php-media-type.html)
- Support for [default value](https://www.opis.io/json-schema/1.x/default-value.html)
- Support for custom variables using [`$vars` keyword](https://www.opis.io/json-schema/1.x/variables.html)
- Support for custom filters using [`$filters` keyword](https://www.opis.io/json-schema/1.x/filters.html)
- Advanced schema reuse using [`$map` keyword](https://www.opis.io/json-schema/1.x/mappers.html)
- Support for [json pointers](https://www.opis.io/json-schema/1.x/pointers.html) (absolute and relative pointers)
- Support for [URI templates](https://www.opis.io/json-schema/1.x/uri-template.html)

## Installation

This library is available on [Packagist](https://packagist.org/packages/opis/json-schema) and can be installed using [Composer](http://getcomposer.org).

```bash
composer require opis/json-schema
```

## Requirements

* PHP 7 or higher

## License

**Opis Json Schema** is licensed under the [Apache License, Version 2.0](http://www.apache.org/licenses/LICENSE-2.0). 

## Documentation

We provide documentation for all json schema keywords, structure, and of course the API
for this library. Check out the documentation at [opis.io/json-schema](https://www.opis.io/json-schema).

Current implementation extends standards by adding 
[`$vars`](https://www.opis.io/json-schema/1.x/variables.html), 
[`$filters`](https://www.opis.io/json-schema/1.x/filters.html) 
and [`$map`](https://www.opis.io/json-schema/1.x/mappers.html) keywords.

## Examples

For more examples please go to the [documentation page](https://www.opis.io/json-schema).

#### Basic example

```php
<?php

use Opis\JsonSchema\{
    Validator,
    ValidationResult,
    ValidationError
};

$validator = new Validator();

$schema = <<<'JSON'
{
    "type": "string",
    "minLength": 3
}
JSON;

/** @var ValidationResult $result */
$result = $validator->dataValidation("abc", $schema);

if ($result->isValid()) {
    echo "Valid", PHP_EOL;
}
else {
    /** @var ValidationError $error */
    $error = $result->getFirstError();
    echo "Invalid, error on keyword: ", $error->keyword(), PHP_EOL;
}
```

#### Using a schema document loader

```php
<?php

use Opis\JsonSchema\{
    Validator,
    ValidationResult,
    ValidationError,
    Loaders\Memory as MemoryLoader
};

$loader = new MemoryLoader();

$loader->add(<<<'JSON'
{
    "$id": "urn:positive-integer",
    
    "type": "integer",
    "minimum": 0
}
JSON
);

$loader->add(<<<'JSON'
{
    "$id": "urn:mail",
    
    "type": "string",
    "format": "email"
}
JSON
);

$loader->add(<<<'JSON'
{
    "$id": "urn:simple-person",
    
    "type": "object",
    "properties": {
        "age": {"$ref": "urn:positive-integer"},
        "mail": {"$ref": "urn:mail"}
    },
    "required": ["age", "mail"],
    "additionalProperties": false
}
JSON
);

$validator = new Validator();
$validator->setLoader($loader);

/** @var ValidationResult $result */
$result = $validator->uriValidation("someone@example.com", "urn:mail");
if ($result->isValid()) {
    echo "Valid e-mail", PHP_EOL;
}
else {
    /** @var ValidationError $error */
    $err = $result->getFirstError();
    echo "Invalid e-mail, error on keyword: ", $err->keyword(), PHP_EOL;
}

/** @var ValidationResult $result */
$result = $validator->uriValidation((object) [
    "age" => 23,
    "mail" => "someone@example.com",
], "urn:simple-person");

if ($result->isValid()) {
    echo "Valid simple-person", PHP_EOL;
}
else {
    /** @var ValidationError $error */
    $err = $result->getFirstError();
    echo "Invalid simple-person, error on keyword: ", $err->keyword(), PHP_EOL;
}
```

#### Using variables ($vars) to dynamically load sub-schemas

```php
<?php

use Opis\JsonSchema\{
    Validator,
    ValidationResult,
    ValidationError
};

$schema = <<<'JSON'
{
    "type": "object",
    "properties": {
        "region": {
            "enum": ["eu", "us"]
        },
        "age": {
            "$ref": "#/{+globalVar}/{+localVar}-{+dataRefVar}",
            "$vars": {
                "localVar": "age",
                "dataRefVar": {
                    "$ref": "1/region"
                }
            }
        }
    },
    "required": ["region"],
    
    "definitions": {
        "age-eu": {
            "type": "integer",
            "minimum": 18
        },
        "age-us": {
            "type": "integer",
            "minimum": 21
        }
    }
}
JSON;

$validator = new Validator();

// Set global variables 
$validator->setGlobalVars([
    'globalVar' => 'definitions'
]);

/** @var ValidationResult $result */
$result = $validator->dataValidation((object) [
    "age" => 20,
    "region" => "eu",
], $schema);

if ($result->isValid()) {
    echo "Valid", PHP_EOL;
}
else {
    /** @var ValidationError $error */
    $err = $result->getFirstError();
    echo "Invalid, error on keyword: ", $err->keyword(), PHP_EOL;
}
```

#### Using a custom filter ($filters)

```php
<?php

use Opis\JsonSchema\{
    Validator,
    ValidationResult,
    ValidationError,
    FilterContainer,
    IFilter
};

$filters = new FilterContainer();

$filters->add("number", "modulo", new class implements IFilter {
    public function validate($data, array $args): bool {
        $d = $args['divisor'] ?? 1;
        $r = $args['reminder'] ?? 0;
        return $data % $d == $r;
    }
});

$validator = new Validator();
$validator->setFilters($filters);

$schema = <<<'JSON'
{
    "type": "integer",
    "$filters": {
        "$func": "modulo",
        "$vars": {
            "divisor": 4,
            "reminder": 3
        }
    }
}
JSON;

/** @var ValidationResult $result */
$result = $validator->dataValidation(7, $schema);
if ($result->isValid()) {
    echo "Valid", PHP_EOL;
}
else {
    /** @var ValidationError $error */
    $err = $result->getFirstError();
    echo "Invalid, error on keyword: ", $err->keyword(), PHP_EOL;
}
```

#### Using a mapper ($map)

```php
<?php

use Opis\JsonSchema\{
    Validator,
    ValidationResult,
    ValidationError,
    Loaders\Memory as MemoryLoader
};

$loader = new MemoryLoader();

$loader->add(<<<'JSON'
{
    "$id": "urn:basic-user",
    
    "type": "object",
    "properties": {
        "name": {
            "type": "string",
            "minLength": 2,
            "maxLength": 120
        },
        "email": {
            "type": "string",
            "format": "email"
        }
    },
    "required": ["name", "email"],
    "additionalProperties": false
}
JSON
);

$loader->add(<<<'JSON'
{
    "$id": "urn:born-user",
    
    "type": "object",
    "properties": {
        "birthday": {
            "type": "string",
            "format": "date"
        }
    },
    "required": ["fullName", "primaryEmail", "birthday"],
    
    "allOf": [
        {
            "$ref": "urn:basic-user",
            "$map": {
                "name": {
                    "$ref": "0/fullName"
                },
                "email": {
                    "$ref": "0/primaryEmail"
                }
            }
        }
    ]
}
JSON
);

$validator = new Validator();
$validator->setLoader($loader);

$user = (object) [
    "fullName" => "John Doe",
    "primaryEmail" => "john.doe@example.com",
    "birthday" => "1970-01-01",
];

/** @var ValidationResult $result */
$result = $validator->uriValidation($user, "urn:born-user");

if ($result->isValid()) {
    echo "Valid", PHP_EOL;
}
else {
    /** @var ValidationError $error */
    $error = $result->getFirstError();
    echo "Invalid, error on keyword: ", $error->keyword(), PHP_EOL;
}
```