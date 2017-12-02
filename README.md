Opis Json Schema
====================
[![Build Status](https://travis-ci.org/opis/json-schema.png)](https://travis-ci.org/opis/json-schema)
[![Latest Stable Version](https://poser.pugx.org/opis/json-schema/v/stable.png)](https://packagist.org/packages/opis/json-schema)
[![Latest Unstable Version](https://poser.pugx.org/opis/json-schema/v/unstable.png)](https://packagist.org/packages/opis/json-schema)
[![License](https://poser.pugx.org/opis/json-schema/license.png)](https://packagist.org/packages/opis/json-schema)

Json Schema
-----------

**Opis Json Schema** is a PHP implementation for the latest [json-schema](http://json-schema.org/) validation draft.

**The library's key features:**

- Fast validation (you can set maximum number of errors for a validation)
- Support for absolute and relative json pointers
- Support for if-then-else
- Support for uri-templates and variables
- Most of the string formats are supported
- Support for custom formats
- Support for default value
- Support for custom filters


## License

**Opis Json Schema** is licensed under the [Apache License, Version 2.0](http://www.apache.org/licenses/LICENSE-2.0). 

## Requirements

* PHP 7 or higher

## Installation

This library is available on [Packagist](https://packagist.org/packages/opis/json-schema) and can be installed using [Composer](http://getcomposer.org).

```json
{
    "require": {
        "opis/json-schema": "^1.0.0"
    }
}
```


### Documentation

#### Simple validation

```php
<?php

use Opis\JsonSchema\Validator;
use Opis\JsonSchema\Loaders\Memory as MemoryLoader;
use Opis\JsonSchema\FilterContainer;
use Opis\JsonSchema\IFilter;

$validator = new Validator();

// without loader

$result = $validator->dataValidation(6, (object) [
    'type' => 'integer',
    'multipleOf' => 2
]);

if ($result->isValid()) {
    echo "valid";
}
else {
    echo "invalid";
    // $result->getFirstError();
    
    /** @var \Opis\JsonSchema\ValidationError $error */
    // foreach ($result->getErrors() as $error) {
        // do something
    //}
}

// memory loader

$loader = new MemoryLoader();

$loader->add((object) [
    "type" => "integer",
    "minimum" => 0
], "urn:positive-integer");

$loader->add((object) [
    "type" => "string",
    "format" => "email"
], "urn:mail");

$loader->add((object) [
    "type" => "object",
    "properties" => (object) [
        "age" => (object)['$ref' => "urn:positive-integer"],
        "mail" => (object)['$ref' => "urn:mail"],
    ],
    "required" => ["age", "mail"]
], "urn:simple-person");

// use memory loader
$validator->setLoader($loader);

$result = $validator->uriValidation("someone@example.com", "urn:mail");

$result = $validator->uriValidation((object) [
    "age" => 23,
    "mail" => "someone@example.com",
], "urn:simple-person");

// use filters

$filters = new FilterContainer();

$filters->add("number", "modulo", new class implements IFilter {
    /**
     * @inheritDoc
     */
    public function validate($data, array $args): bool {
        $d = $args['divisor'] ?? 1;
        $r = $args['reminder'] ?? 0;
        return $data % $d == $r;
    }
});

$validator->setFilters($filters);

$result = $validator->dataValidation(7, (object) [
    "type" => "integer",
    '$filters' => (object) [
        '$func' => 'modulo',
        '$vars' => (object) [
            'divisor' => 4,
            'reminder' => 3    
        ],
    ]
]);

```

#### Usage of variables ($vars) for $ref (uri-template)

```json
{
    "type": "object",
    "properties": {
        "prop1": {"type": "string"},
        "prop2": {
            "$ref": "http://example.com/{file}.json{#fragment}",
            "$vars": {
                "fragment": "static-fragment",
                "file": {"$ref": "1/prop1"} 
            }
        }
    },
    "required": ["prop1"]
}
```

For the following data
```json
{
    "prop1": "some-file",
    "prop2": null
}
```
the `$ref` will be `http://example.com/absolute/path/some-file.json#static-fragment`

#### Usage of filters in schema ($filters)

```json
{
    "simple": {
        "$filters": {
            "$func": "filter_name"
        }
    },
    "with_vars": {
        "$filters": {
            "$func": "filter_name",
            "$vars": {
                "arg1": 5,
                "arg2": "some arg",
                "arg3": {
                    "$ref": "2/relative/path"
                }
            }
        }
    },
    "multiple": {
        "$filters": [
            {
                "$func": "filter_name_1"
            },
            {
                "$func": "filter_name_2"
            }
        ]
    }
}
```