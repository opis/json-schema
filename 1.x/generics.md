---
layout: project
version: 1.x
title: Generic validation keywords
description: php opis json schema generic validation keywords
keywords: opis, php, json, schema, generic, validation, type, enum, const
---

# Generic validation keywords

These keywords allow you to validate an instance by checking the data type
or by checking if the data value equals to a predefined value.

## Validation keywords

The following keywords are supported by any type, and evaluated
in the presented order. All keywords are optional.

### type

The `type` keyword specifies the data type that a schema will use.
This keyword is not mandatory, and the value of keyword can be a string
representing a [valid data type](structure.html#data-types), or an array of strings representing
valid data types.

```json
{
  "type": "string"
}
```

`"some text"` - valid
{:.alert.alert-success}

`""` - valid (empty string)
{:.alert.alert-success}

`12` - invalid (is integer/number)
{:.alert.alert-danger}

`null` - invalid (is null)
{:.alert.alert-danger}


You can use multiple types at once to restrict accepted data types,
or you can omit the `type` keyword to accept any type. The order of
types in the array doesn't matter, but you should not put the same
type more than once.

```json
{
  "type": ["object", "null"]
}
```

`{"a": 1}` - valid (is object)
{:.alert.alert-success}

`null` - valid (is null)
{:.alert.alert-success}

`"1, 2, 3"` - invalid (is string)
{:.alert.alert-danger}

`[{"a": 1}, {"b": 2}]` - invalid (is array)
{:.alert.alert-danger}

```json
{
  "type": ["number", "string", "null"]
}
```

`-10.5` - valid (is number)
{:.alert.alert-success}

`"some string"` - valid (is string)
{:.alert.alert-success}

`null` - valid (is null)
{:.alert.alert-success}

`false` - invalid (is boolean)
{:.alert.alert-danger}

`{"a": 1}` - invalid (is object)
{:.alert.alert-danger}

`[1, 2, 3]` - invalid (is array)
{:.alert.alert-danger}

### const

An instance validates against this keyword if its value equals to the
value of this keyword. The value of this keyword can be anything.

```json
{
  "const": "test"
}
```
Validates if equals to `"test"`.
{:.blockquote-footer}

`"test"` - valid
{:.alert.alert-success}

`"Test"` - invalid
{:.alert.alert-danger}

`"tesT"` - invalid
{:.alert.alert-danger}

`3.4` - invalid
{:.alert.alert-danger}

```json
{
  "const": {
    "a": 1,
    "b": "2"
  }
}
```
Validates if the object have the same properties and values (order of properties does not matter).
{:.blockquote-footer}

`{"a": 1, "b": "2"}` - valid
{:.alert.alert-success}

`{"b": "2", "a": 1}` - valid
{:.alert.alert-success}

`{"a": 1, "b": "2", "c": null}` - invalid
{:.alert.alert-danger}

`5.10` - invalid
{:.alert.alert-danger}

### enum

An instance validates against this keyword if its value equals can be
found in the items defined by the value of this keyword. 
The value of this keyword must be an array containing anything.
An empty array is not allowed.

```json
{
  "enum": ["a", "b", 1, null]
}
```

`"a"` - valid
{:.alert.alert-success}

`"b"` - valid
{:.alert.alert-success}

`1` - valid
{:.alert.alert-success}

`null` - valid
{:.alert.alert-success}

`"A"` - invalid
{:.alert.alert-danger}

`-1` - invalid
{:.alert.alert-danger}

`false` - invalid
{:.alert.alert-danger}

`["a", "b", 1, null]` - invalid
{:.alert.alert-danger}

