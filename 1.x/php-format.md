---
layout: project
version: 1.x
title: Creating Opis Json Schema formats
description: the opis json schema validation using custom formats
keywords: opis, json, schema, validation, format
---

# Creating custom formats

A custom format is an object implementing `\Opis\JsonSchema\IFormat` interface.
The `validate` method receives the `mixed $data` as argument and must return 
a `boolean` (`true` if the `$data` has the specified format, `false` otherwise).

```php
<?php

use Opis\JsonSchema\IFormat;

class PrimeNumberFormat implements IFormat
{
    public function validate($data): bool {
        if ($data < 2) {
            return false;
        }
        if ($data == 2) {
            return true;
        }
        $max = floor(sqrt($data)) + 1;
        for ($i = 3; $i < $max; $i += 2) {
            if ($data % $i == 0) {
                return false;
            }
        }
        
        return true;
    }
}
```

## Using formats

Before using the [`format` keyword](formats.html) in your schemas, make sure
to register them in a `Opis\JsonSchema\IFormatContainer` object, and pass
that object to [`Opis\JsonSchema\IValidator::setFormats()`](php-validator.html#setformats).
- json data type (number, integer, string, array, object)
- name: the name you will use in your schemas
- the format object that implements `Opis\JsonSchema\IFormat`

```php
<?php

use Opis\JsonSchema\{
    Validator,
    FormatContainer
};

// Create a new FormatContainer
$formats = new FormatContainer();

// Register our prime format
$formats->add("integer", "prime", PrimeNumberFormat());

// Create a IValidator
$validator = new Validator();

// Set formats to be used by validator
$validator->setFormats($formats);

// Validation ...

```

Here is an example that uses our prime number format

```json
{
  "type": "integer",
  "format": "prime"
}
```

This schema validates `5`, but does not validate `9` (3 * 3).