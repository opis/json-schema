---
layout: project
version: 1.x
title: Variables ($vars)
description: using variables in json schema
keywords: opis, json, schema, variables, $vars
---

# Variables

Variables are a non-standard addition to tackle
different scenarios where json schema alone fails.
This means, that you can use a new keyword named `$vars`
to make your life easier.

`$vars` can be used as:

- substitution variables for [URI templates](uri-template.html)
- arguments to [filters](filters.html)

## General structure

In a json schema document, `$vars` must be an object but 
the values inside `$vars` can be anything.


`$vars` keyword support is enabled by default, to disable it use `Opis\JsonSchema\Validator::varsSupport(false)`.
{:.alert.alert-info}

Example of `$vars`:

```json
{
  "$vars": {
    "some-number": 123,
    "some-data": "data",
    "my-array": [1, 2, null, "str"],
    "the-object": {
      "a": 10,
      "b": 5
    }
  }
}
```

### Data reference values

In the above example, `$vars` contains only _constant_ values, 
making you wonder why it was named like so. The reason is simple,
because you can access the current data being validated using
[json pointers](pointers.html).

To do this, you will reference a value in the same way you reference
other schemas: by using `$ref` keyword.

Take a look at the next example:

```json
{
  "$vars": {
    "value-of-prop-a": {"$ref": "/a"},
    "secret-value": {"$ref": "/deep/secret"},
    "some-constant": 5
  }
}
```

considering that we validate the following data

```json
{
  "a": "A",
  "deep": {
    "secret": "S"
  },
  "constant": 10
}
```

the `$vars` will become

```json
{
  "value-of-prop-a": "A",
  "secret-value": "S",
  "some-constant": 5
}
```

As you can see, the `$ref` contains an [absolute json pointer](pointers.html#absolute-pointers), but
can also use [relative json pointers](pointers.html#relative-pointers).

Here is an example using a schema:

```json
{
  "$id": "http://example.com/vars-example-1#",
  "type": "object",
  "properties": {
    "prop-a": {
      "type": "string"
    },
    "prop-b": {
      "$vars": {
        "value-of-prop-a": {"$ref": "1/prop-a"},
        "some-constant": 5
      }
    }
  }
}
```

considering that data we validate is

```json
{
  "prop-a": "A",
  "prop-b": "B"
}
```

then the `$vars` will become

```json
{
  "value-of-prop-a": "A",
  "some-constant": 5
}
```

You might have some questions right now, but here is a list of Q&A to get a
better understanding of what's happening.

- Why `1/prop-a` and not `0/prop-a`? 

Because `0` points to value `"A"`, 
which is a string and doesn't have a `prop-a` key to descend into, but `1` points to the object
that holds the property `prop-a`, so it can descend.

- What about `2/prop-a`?

Well, in our example it is impossible to ascend 2 levels because at level 1
we are already at the root. So an error will be thrown.

- Where can I find more info about these pointers?

Go to the [Json Pointers](pointers.html) page.

- Where is this useful? 

See below.

## Variables and URI templates

`$vars` can act as substitutions/variables in [URI templates](uri-template.html). In other words,
you can dynamically reference schemas based on the current data being validated.

Please note that data can come from an untrusted source,
so it is better to filter it before by using `enum` or other json schema
keywords including [$filters](filters.html) if needed. Also, use URI template
escaping mechanisms.
{:.alert.alert-warning}

Here is an example that validates a number by using number's type.

```json
{
    "$id": "http://example.com/number#",
    "type": "object",
    "properties": {
        "type": {
          "type": "string",
          "enum": ["natural", "integer", "real", "complex"]
        },
        "value": {
          "$ref": "#/definitions/{+number-type}",
          "$vars": {
            "number-type": {"$ref": "1/type"}
          }
        }
    },
    "required": ["type", "value"],
    
    "definitions": {
      "natural": {
        "type": "integer",
        "minimum": 0
      },
      "integer": {
        "type": "integer"
      },
      "real": {
        "type": "number"
      },
      "complex": {
        "type": "object",
        "properties": {
          "a": {
            "type": "number"
          },
          "b": {
            "type": "number"
          }
        },
        "required": ["a", "b"],
        "additionalProperties": false
      }
    }
}
```

First, we see that `$ref` of `value` property is an uri template, having the `number-type` placeholder.
That placeholder will be replaced with the value of the variable having the same name from `$vars`.
So, if `$vars` is `{"number-type": "test"}` the `$ref` will become `#/definitions/test`. 

Second, the `$ref` from `number-type` property of `$vars` denotes a relative
json pointer. Instead of using the object as placeholder, the value will be
resolved, in our case it will use the value of `type` property from our data (not schema).

Consider the following data to be validated against our schema:

```json
{"type": "natural", "value": 58}
```

the steps for validating the `value` property are:

- get `$ref` => `#/definitions/{number-type}`
- resolve `$vars` => `{"number-type": "natural"}`
- replace placeholders in `$ref` => `#/definitions/natural`
- resolve `#/definitions/natural` sub-schema => `{"type": "integer", "minimum": 0}`
- validate `58` against resolved sub-schema => valid 

## Global variables

Depending on your project, sometimes you'll want certain variables
to always be available when validating. 
To do so you must pass them to [validator](php-validator.html) before validating,
 by using `Opis\JsonSchema\Validator::setGlobalVars()`.
To see current list of global variables use `Opis\JsonSchema\Validator::getGlobalVars()`.

### Global variables example

```json
{
    "type": "object",
    "properties": {
        "prop-a": {
            "$ref": "http://example.com/vendor/{VENDOR_VERSION}/a.json"
        },
        "prop-b": {
            "$ref": "http://example.com/vendor/{VENDOR_VERSION}/b.json",
            "$vars": {
                "VENDOR_VERSION": "2.0"
            }
        },
        "prop-c": {
            "$ref": "http://example.com/vendor/{VENDOR_VERSION}/c.json",
            "$vars": {
                "version": 5
            }
        }
    }
}
```

Considering that our global variables are:

```json
{
  "VENDOR_VERSION": "1.0"
}
```

the `$ref`s will be

| Property | Value | Reason |
|---|---|---|
| `prop-a` | `http://example.com/vendor/1.0/a.json` | **global** variable `VENDOR_VERSION` is `1.0` |
| `prop-b` | `http://example.com/vendor/2.0/b.json` | **local** variable `VENDOR_VERSION` is `2.0` |
| `prop-c` | `http://example.com/vendor/1.0/c.json` | **global** variable `VENDOR_VERSION` is `1.0` and there is no local variable `VENDOR_VERSION` |
{:.table.table-striped}
