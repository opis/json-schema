---
layout: project
version: 1.x
title: Opis Json Schema Validation
description: opis json schema validation quick start
keywords: opis, json, schema, validation, quick start
---

# Quick start

First things first: install the library

```bash
composer require opis/json-schema
```

## Simple data validation

```php
<?php

use Opis\JsonSchema\{
    Validator, ValidationResult, ValidationError
};

// Our schema
$schema = <<<'JSON'
{
    "type": "string",
    "minLength": 3
}
JSON;

// We first create a new validator,
// because we can reuse this instance later
$validator = new Validator();

// Our data that will be validated
$data = "abc";

/** @var ValidationResult $result */
$result = $validator->dataValidation($data, $schema);

if ($result->isValid()) {
    echo $data, " is valid", PHP_EOL;
} else {
    /** @var ValidationError $error */
    $error = $result->getFirstError();
    echo $data, " is invalid", PHP_EOL;
    echo "Error: ", $error->keyword(), PHP_EOL;
    echo json_encode($error->keywordArgs(), JSON_PRETTY_PRINT), PHP_EOL;
}
```

The output of the above snippet is

```text
abc is valid
```

If we change the value of `$data` to `3`, the output becomes

```text
3 is invalid
Error: type
{
    "expected": "string",
    "used": "integer"
}
```

If we change the value of `$data` to `"ab"`, the output becomes

```text
ab is invalid
Error: minLength
{
    "min": 3,
    "length": 2
}
```

## Using a loader

```php
<?php

use Opis\JsonSchema\{
    Validator,
    ValidationResult,
    ValidationError,
    Loaders\Memory as MemoryLoader
};

// Create a new validator instance
$validator = new Validator();

// Create a loader, this will help us fetch
// schema documents by $id from memory
$loader = new MemoryLoader();

// Add the person.json schema
$loader->add(<<<'JSON'
{
    "$id": "http://example.com/person.json",
    
    "type": "object",
    "properties": {
        "name": {
            "type": "string",
            "minLength": 3
        },
        "age": {
            "type": "integer",
            "minimum": 18
        },
        "address": {
            "$ref": "/address.json"
        }
    },
    "required": ["name", "age", "address"],
    "additionalProperties": false
}
JSON
);

// Add address.json schema.
// This is referenced from person.json.
$loader->add(<<<'JSON'
{
    "$id": "http://example.com/address.json",
    
    "type": "object",
    "properties": {
        "city": {
            "type": "string"
        },
        "street": {
            "type": "string"
        },
        "number": {
            "type": "integer"
        }
    },
    "required": ["city", "street"],
    "additionalProperties": false   
}
JSON
);

// Set our new loader
$validator->setLoader($loader);

// The person that will be validated
$person = (object) [
    "name" => "John Doe",
    "age" => 33,
    "address" => (object) [
        "city" => "New York",
        "street" => "Sesame",
        "number" => 5
    ]
];

/** @var ValidationResult $result */
$result = $validator->uriValidation($person, "http://example.com/person.json");

if ($result->isValid()) {
    echo $person->name, " is a valid person", PHP_EOL;
}
else {
    echo "Invalid person", PHP_EOL;

    /** @var ValidationError $error */
    $error = $result->getFirstError();
    echo "Error: ", $error->keyword(), PHP_EOL;
    echo json_encode($error->keywordArgs(), JSON_PRETTY_PRINT), PHP_EOL;
}
```

The output of the above code snippet is

```text
John Doe is a valid person
```

If we change our `$person` to something like this

```php
$person = (object) [
    "name" => "John Doe",
    "age" => 2,
    "address" => (object) [
        "city" => "New York",
        "street" => "Sesame",
        "number" => 5
    ]
];
```

the output will be

```text
Invalid person
Error: minimum
{
    "min": 18
}
```