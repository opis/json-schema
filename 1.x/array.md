---
layout: project
version: 1.x
title: Array type
description: php opis json schema validation of arrays
keywords: opis, php, json, schema, array, validation
---

# Array type

The `array` type is used for validating ordered lists (indexed arrays).

```json
{
  "type": "array"
}
```

`[]` - valid (empty array)
{:.alert.alert-success}

`[2, 1, "str", false, null, {}]` - valid
{:.alert.alert-success}

`12` - invalid (is integer/number)
{:.alert.alert-danger}

`null` - invalid (is null)
{:.alert.alert-danger}

`"1, 2, 3"` - invalid (is string)
{:.alert.alert-danger}

`{"0": 1, "1": 2, "2": 3}` - invalid (is object)
{:.alert.alert-danger}

## Validation keywords

The following keywords are supported by the `array` type, and evaluated
in the presented order. All keywords are optional.

### minItems

An array is valid against this keyword, if the number of items it contains 
is greater than, or equal to, the value of this keyword.
The value of this keyword must be a non-negative integer.

```json
{
  "type": "array",
  "minItems": 2
}
```

Array must have at least `2` items.
{:.blockquote-footer}

`[1, 2, 3]` - valid (3 > 2)
{:.alert.alert-success}

`["a", "b"]` - valid (2 = 2)
{:.alert.alert-success}

`["text"]` - invalid (1 < 2)
{:.alert.alert-danger}

`[]` - invalid (0 < 2)
{:.alert.alert-danger}

### maxItems

An array is valid against this keyword, if the number of items it contains
is lower than, or equal to, the value of this keyword.
The value of this keyword must be a non-negative integer.

```json
{
  "type": "array",
  "maxItems": 2
}
```

Array can have at most `2` items.
{:.blockquote-footer}

`[1, 2]` - valid (2 = 2)
{:.alert.alert-success}

`["a"]` - valid (1 < 2)
{:.alert.alert-success}

`[]` - valid (0 < 2)
{:.alert.alert-success}

`[1, 2, 3]` - invalid (3 > 2)
{:.alert.alert-danger}

### uniqueItems

An array is valid against this keyword if an item cannot be found
more than once in the array.
The value of this keyword must be a boolean. If set to `false` the keyword
validation will be ignored.

```json
{
  "type": "array",
  "uniqueItems": true
}
```

Array must have unique items (for every data type).
{:.blockquote-footer}

`[1, 2, 3]` - valid
{:.alert.alert-success}

`["a", "b", "c"]` - valid
{:.alert.alert-success}

`[1, "1"]` - valid
{:.alert.alert-success}

`[[1, 2], [3, 4]]` - valid
{:.alert.alert-success}

`[1, 2, 1]` - invalid (duplicate `1`)
{:.alert.alert-danger}

`["a", "b", "B", "a"]` - invalid (duplicate `a`)
{:.alert.alert-danger}

`[[1, 2], [1, 3], [1, 2]]` - invalid (duplicate `[1, 2]`)
{:.alert.alert-danger}

`[{"a": 1, "b": 2}, {"a": 1, "c": 2}, {"a": 1, "b": 2}]` - invalid (duplicate `{"a": 1, "b": 2}`)
{:.alert.alert-danger}

### contains

An array is valid against this keyword if at least one item is valid against
the schema defined by the keyword value.
The value of this keyword must be a valid json schema (object or boolean).

Please not that an empty array will never be valid against this keyword.
{:.alert.alert-info}

```json
{
  "type": "array",
  "contains": {
    "type": "integer"
  }
}
```

Array must contain at least one integer.
{:.blockquote-footer}

`[1]` - valid
{:.alert.alert-success}

`[1, 2]` - valid
{:.alert.alert-success}

`["a", "b" -4.0]` - valid
{:.alert.alert-success}

`[]` - invalid
{:.alert.alert-danger}

`["a", "b", "1"]` - invalid
{:.alert.alert-danger}

`[2.3, 4.5, -6.7]` - invalid
{:.alert.alert-danger}

### items

An array is valid against this keyword if items are valid against the
corresponding schemas provided by the keyword value. The value of
this keyword can be
- a valid json schema (object or boolean), then every item must be valid
against this schema
- an array of valid json schemas, then each item must be valid against
the schema defined at the same position (index). Items that don't have a corresponding
position (array contains 5 items and this keyword only has 3) 
will be considered valid, unless the [`additionalItems` keyword](#additionalitems)
is present - which will decide the validity.

```json
{
  "type": "array",
  "items": {
    "type": "integer",
    "minimum": 0
  }
}
```

Array must contain only positive integers.
{:.blockquote-footer}

`[1, 2, 3]` - valid
{:.alert.alert-success}

`[-0, 2.0]` - valid
{:.alert.alert-success}

`[]` - valid
{:.alert.alert-success}

`[-2, 3, 4]` - invalid
{:.alert.alert-danger}

`["a", 2]` - invalid
{:.alert.alert-danger}

```json
{
  "type": "array",
  "items": [
    {"type": "integer"},
    {"type": "string"}
  ]
}
```

First item of the array must be an integer and the second a string.
Other items can be anything.
{:.blockquote-footer}

`[1, "a"]` - valid
{:.alert.alert-success}

`[1.0, "a", 5.6, null, true]` - valid
{:.alert.alert-success}

`[1]` - valid
{:.alert.alert-success}

`[]` - valid
{:.alert.alert-success}

`["a", 1]` - invalid
{:.alert.alert-danger}

`[5.5, "a"]` - invalid
{:.alert.alert-danger}

`[5, 6]` - invalid
{:.alert.alert-danger}

### additionalItems

An array is valid against this keyword if all _unchecked_ items
are valid against the schema defined by the keyword value.
An item is considered _unchecked_ if [`items` keyword](#items) contains
an array of schemas and doesn't have a corresponding position (index).
If the `items` keyword is not an array, then this keyword is ignored.
The value of the keyword must be a valid json schema (object, boolean).

```json
{
  "type": "array",
  "items": [
    {"type": "integer"},
    {"type": "string"}
  ],
  "additionalItems": {
    "type": "boolean"
  }
}
```

First item of the array must be an integer and the second a string.
Other items can only be booleans.
{:.blockquote-footer}

`[1, "a", true, false, true, true]` - valid
{:.alert.alert-success}

`[1, "a"]` - valid
{:.alert.alert-success}

`[1]` - valid
{:.alert.alert-success}

`[]` - valid
{:.alert.alert-success}

`[1, "a", 2]` - invalid
{:.alert.alert-danger}

`[1, "a", true, 2, false]` - invalid
{:.alert.alert-danger}

`[1, true, false]` - invalid
{:.alert.alert-danger}