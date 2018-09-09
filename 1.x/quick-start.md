---
layout: project
version: 1.x
title: Quick start
description: A short overview of the Opis JSON Schema library and of the JSON Schema standard
keywords: opis, json, schema, validation, quick start
---
# Quick start

* [The setup](#the-setup)
* [The validator](#the-validator)
* [The PHP part](#the-php-part)

## The setup

To better exemplify the benefits of using JSON Schema for validating JSON documents, were gonna build a validator for
a set of data that represents the profile of some web application's user.
For the sake of simplicity we don't care how we obtained these data, and we will simply assume that they are stored
in a variable called `$data`. 

Here is a possible content for this variable:
```json
{
    "name": "John Doe",
    "age": 31,
    "email": "john@example.com",
    "website": null,
    "location": {
        "country": "US",
        "address": "Sesame Street, no. 5"
    },
    "available_for_hire": true,
    "interests": ["php", "html", "css", "javascript", "programming", "web design"],
    "skills": [
        {
            "name": "HTML",
            "value": 100
        },
        {
            "name": "PHP",
            "value": 55
        },
        {
            "name": "CSS",
            "value": 99.5
        },
        {
            "name": "JavaScript",
            "value": 75
        }
    ]
}
```

## The validator

Now that we have an idea over how our data are structured, its time to start creating validation schema. 
The JSON contained in `$data` is structured as an object with multiple
properties. All the properties are required to be present and adding additional properties is forbidden.
The validation schema that follows these rules, looks something like bellow.

```json
{
    "$schema": "http://json-schema.org/draft-07/schema#",
    "$id": "http://api.example.com/profile.json#",
    "type": "object",
    "properties": {
    },
    "required": ["name", "age", "email", "location", 
                 "available_for_hire", "interests", "skills"],
    "additionalProperties": false
}
```

Neither the [$schema](structure.html#schema-keyword), nor the [$id](structure.html#id-keyword) keywords are required
in order for our validation schema to work. We only added them as an example of good practice.

### Object's properties

The only thing that is missing from the above validation schema are the validation rules for its properties.
Let's take each of the object's property in turn, explain it, and write some validation rules for them.

##### name

The `name` property must be a non-empty string that contains at most 64 characters.
We also added a [pattern](string.html#pattern) constraint, to make sure that the value of the `name` property
follows a specific format.

```json
{
    "name": {
        "type": "string",
        "minLength": 1,
        "maxLength": 64,
        "pattern": "^[a-zA-Z0-9\\-]+(\\s[a-zA-Z0-9\\-]+)*$"
    }
}
```

##### age

The `age` property is an integer between 18 and 100.

```json
{
    "age": {
        "type": "integer",
        "minimum": 18,
        "maximum": 100
    }
}
```

##### email

This is a string that must be formatted as an email and its maximum length is of 128 characters.

```json
{
    "email": {
        "type": "string",
        "maxLength": 128,
        "format": "email"
    }
}
```

##### website

If the user doesn't have a website, then the value of this property must be `null`. Otherwise this is
a string of a maximum length of 128 characters, formatted as a [hostname](formats.html#hostname).

```json
{
    "website": {
        "type": ["string", "null"],
        "maxLength": 128,
        "format": "hostname"
    }
}
```

##### location

This property is an object that contains two other properties: `country` and `address`. 
The `country` property is a two-letter string representing the country's code.
To keep things simple we only support United State, Canada and United Kingdom. 
Therefore we could just use an [enum](generics.html#enum). The `address` property
is just a string that can contains at most 128 characters. 
Both these properties are required, and the object doesn't support aditional properties.

```json
{
    "location": {
        "type": "object",
        "properties": {
             "country": {
                 "enum": ["US", "CA", "UK"]
             },
             "address": {
                 "type": "string",
                 "maxLength": 128
             }
        },
        "required": ["country", "address"],
        "additionalProperties": false
    }
}
```

##### available_for_hire

The property's value must be a boolean

```json
{
    "available_for_hire": {
        "type": "boolean"
    }
}
```

##### interests

This must be an array of strings. It must contain at least 3 items and a maximum of 100. 
Each string within the array must have a maximum length of 64 characters and must be unique to the array.

```json
{
    "interests": {
        "type": "array",
        "minItems": 3,
        "maxItems": 100,
        "uniqueItems": true,
        "items": {
            "type": "string",
            "maxLength": 120
        }
    }
}
```

##### skills

This is also an array, but this one contains objects. Each object has two required properties: `name` and `value`.
The `name` properties represents the name of the skill that user have, and it's a non-empty string of a maximum
length of 64 characters. The `value` property tells how skilled is the user using a float number between 0 and 100.
That number can be expressed as multiples of *0.25*. Each object within the array must be unique and it doesn't 
accept new propeties. 
There isn't a minimum number of skills that a user can have, so the array can be empty, 
but there is a maximum of 100 skills that can be declared.


```json
{
    "skills": {
        "type": "array",
        "maxItems": 100,
        "uniqueItems": true,
        "items": {
            "type": "object",
            "properties": {
                "name": {
                    "type": "string",
                    "minLenght": 1,
                    "maxLength": 64
                },
                "value": {
                    "type": "number",
                    "minimum": 0,
                    "maximum": 100,
                    "multipleOf": 0.25
                }
            },
            "required": ["name", "value"],
            "additionalProperties": false
        }
    }
}
```

### Putting all together

Now that we have finished defining validation rules, let's put all together and see the resulting validation schema.

```json
{
    "$schema": "http://json-schema.org/draft-07/schema#",
    "$id": "http://api.example.com/profile.json#",
    "type": "object",
    "properties": {
        "name": {
            "type": "string",
            "minLength": 1,
            "maxLength": 64,
            "pattern": "^[a-zA-Z0-9\\-]+(\\s[a-zA-Z0-9\\-]+)*$"
        },
        "age": {
            "type": "integer",
            "minimum": 18,
            "maximum": 100
        },
        "email": {
            "type": "string",
            "maxLength": 128,
            "format": "email"
        },
        "website": {
            "type": ["string", "null"],
            "maxLength": 128,
            "format": "hostname"
        },
        "location": {
            "type": "object",
            "properties": {
                 "country": {
                     "enum": ["US", "CA", "UK"]
                 },
                 "address": {
                     "type": "string",
                     "maxLength": 128
                 }
            },
            "required": ["country", "address"],
            "additionalProperties": false
        },
        "available_for_hire": {
            "type": "boolean"
        },
        "interests": {
            "type": "array",
            "minItems": 3,
            "maxItems": 100,
            "uniqueItems": true,
            "items": {
                "type": "string",
                "maxLength": 120
            }
        },
        "skills": {
            "type": "array",
            "maxItems": 100,
            "uniqueItems": true,
            "items": {
                "type": "object",
                "properties": {
                    "name": {
                        "type": "string",
                        "minLenght": 1,
                        "maxLength": 64
                    },
                    "value": {
                        "type": "number",
                        "minimum": 0,
                        "maximum": 100,
                        "multipleOf": 0.25
                    }
                },
                "required": ["name", "value"],
                "additionalProperties": false
            }
        }
    },
    "required": ["name", "age", "email", "location", 
                 "available_for_hire", "interests", "skills"],
    "additionalProperties": false
}
```

## The PHP part

Believe it or not, the PHP part is the most trivial part of the validation process.
Once we have the validation schema, we can save it into a file like `schema.json` and
start validating the content of `$data`.

```php
use Opis\JsonSchema\{
    Validator, ValidationResult, ValidationError, Schema
};


$data = json_decode($data);
$schema = Schema::fromJsonString(file_get_contents('/path/to/schema.json'));

$validator = new Validator();

/** @var ValidationResult $result */
$result = $validator->schemaValidation($data, $schema);

if ($result->isValid()) {
    echo '$data is valid', PHP_EOL;
} else {
    /** @var ValidationError $error */
    $error = $result->getFirstError();
    echo '$data is invalid', PHP_EOL;
    echo "Error: ", $error->keyword(), PHP_EOL;
    echo json_encode($error->keywordArgs(), JSON_PRETTY_PRINT), PHP_EOL;
}
```