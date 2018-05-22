---
layout: project
version: 1.x
title: Applying multiple subschemas
description: php opis json schema applyingmultiple subschemas with allOf, anyOf, oneOf
keywords: opis, php, json, schema, multiple subschemas, allOf, anyOf, oneOf
---

# Applying multiple subschemas

It is a common practice to validate something against multiple subschemas,
and the following keywords will help you combine subschemas into one validator.

## Validation keywords

The following keywords are supported by any instance type, and are evaluated in
the presented order. All keywords are optional.

### anyOf

An instance is valid against this keyword if is valid against **at least one** schema
defined by the value of this keyword. 
The value of this keyword must be an array of valid json schemas (objects or booleans).

Please note that Opis Json Schema will stop checking other subschemas once
a subschema validates the instance. This is done for performance reasons.
{:.alert.alert-info}

```json
{
  "type": "array",
  "anyOf": [
    {
      "contains": {"const": 0}
    },
    {
      "contains": {"const": "ok"}
    }
  ]
}
```

The array is valid if contains `0` or `"ok"`.  Am empty array is not valid.
{:.blockquote-footer}

`["a", 1, 0, 2]` - valid (one subschema matched)
{:.alert.alert-success}

`["a", 0, "ok", 2]` - valid (two subschemas matched)
{:.alert.alert-success}

`["a", "b"]` - invalid (no subschema matched)
{:.alert.alert-danger}

`[]` - invalid (no subschema matched)
{:.alert.alert-danger}

Please pay attention when using `anyOf`! You can write schemas that never
validate!
{:.alert.alert-warning}

```json
{
  "type": "string",
  "anyOf": [
    {"const": 0},
    {"const": 1}
  ]
}
```

This is never valid.
{:.blockquote-footer}

### oneOf

An instance is valid against this keyword if is valid against **exactly one** schema
defined by the value of this keyword. 
The value of this keyword must be an array of valid json schemas (objects or booleans).

Please note that Opis Json Schema will stop checking other subschemas once
two subschemas validate the instance. This is done for performance reasons.
{:.alert.alert-info}

```json
{
  "type": "array",
  "items": {
    "type": "number"  
  },
  "oneOf": [
    {
      "items": {
        "exclusiveMinimum": 0
      }
    },
    {
      "items": {
        "exclusiveMaximum": 0
      }
    },
    {
      "items": {
        "const": 0
      }
    }
  ]
}
```

The array is valid in one of these cases: contains only positive numbers,
contains only negative numbers, or contains only zeroes. 
Am empty array is not valid.
{:.blockquote-footer}

`[1, 2, 3]` - valid
{:.alert.alert-success}

`[-1, -2, -3]` - valid
{:.alert.alert-success}

`[0, -0, 0.0]` - valid
{:.alert.alert-success}

`[-1, 1]` - invalid (two subschemas matched)
{:.alert.alert-danger}

`[-1, 0]` - invalid (two subschemas matched)
{:.alert.alert-danger}

`[1, 0]` - invalid (two subschemas matched)
{:.alert.alert-danger}

`[-1, 0, 1]` - invalid (three subschemas matched)
{:.alert.alert-danger}

`[]` - invalid (no subschema matched)
{:.alert.alert-danger}

Please pay attention when using `oneOf`! You can write schemas that never
validate!
{:.alert.alert-warning}

```json
{
  "oneOf": [
    {"const": 0},
    {"enum": [0, 1, 2]}
  ]
}
```

This is never valid.
{:.blockquote-footer}

### allOf

An instance is valid against this keyword if is valid against **all** schemas
defined by the value of this keyword. 
The value of this keyword must be an array of valid json schemas (objects or booleans).

```json
{
  "allOf": [
    {"minLength": 2},
    {"pattern": "^a"}
  ]
}
```

If instance is a `string` then it must have a minimum length of `2` and start with `a`.
{:.blockquote-footer}

`"abc"` - valid
{:.alert.alert-success}

`"ab"` - valid
{:.alert.alert-success}

`2` - valid
{:.alert.alert-success}

`[1, 2, 3]` - valid
{:.alert.alert-success}

`"a"` - invalid (length is `1`)
{:.alert.alert-danger}

`"Ab"` - invalid (must start with `a`)
{:.alert.alert-danger}

Please pay attention when using `allOf`! You can write schemas that never
validate!
{:.alert.alert-warning}

```json
{
  "allOf": [
    {"type": "string"},
    {"type": "number"}
  ]
}
```

This is never valid.
{:.blockquote-footer}