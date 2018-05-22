---
layout: project
version: 1.x
title: Object type
description: php opis json schema validation of objects
keywords: opis, php, json, schema, object, validation
---

# Object type

The `object` type is used for validating key-value maps (objects).

```json
{
  "type": "object"
}
```

`{}` - valid (object with nu properties)
{:.alert.alert-success}

`{"prop1": "val1", "prop2": 2.5}` - valid
{:.alert.alert-success}

`12` - invalid (is integer/number)
{:.alert.alert-danger}

`null` - invalid (is null)
{:.alert.alert-danger}

`"some text"` - invalid (is string)
{:.alert.alert-danger}

## Validation keywords

The following keywords are supported by the `object` type, and evaluated
in the following order: `required`, `dependencies`, `minProperties`, `maxProperties`,
`propertyNames`, `properties`, `patternProperties`, `additionalProperties`. 
All keywords are optional.

### properties

An object is valid against this keyword if every property
that is present in both the object and the value of this keyword,
 validates against the corresponding schema.
The value of this keyword must be an object, where properties must
contain valid json schemas (objects or booleans). Only the property names
that are present in both the object and the keyword value are checked.

```json
{
  "type": "object",
  "properties": {
    "a": {
      "type": "string"
    },
    "b": {
      "type": "integer"
    }
  }
}
```

Property `a` must be a string and property `b` must be an
integer, if present.
{:.blockquote-footer}

`{"a": "str", "b": 5}` - valid
{:.alert.alert-success}

`{"a": "str"}` - valid (`a` is a string)
{:.alert.alert-success}

`{"b": 5, "c": null}` - valid (`c` is an integer)
{:.alert.alert-success}

`{"prop1": 0, "prop2": "str"}` - valid (`a` and `b` properties are missing)
{:.alert.alert-success}


`{"a": 1, "b": 5}` - invalid (`a` is not a string)
{:.alert.alert-danger}

`{"a": 1, "b": "text"}` - invalid (`a` is not a string, `b` is not an integer)
{:.alert.alert-danger}

### required

An object is valid against this keyword if it contains all property names (keys)
specified by the value of this keyword. The value of this keyword must be a
non-empty array of strings representing property names.

```json
{
  "type": "object",
  "required": ["a", "b"]
}
```

Object must have both `a` and `b` properties.
{:.blockquote-footer}

`{"a": 1, "b": 2, "c": 3}` - valid
{:.alert.alert-success}

`{"a": 1, "b": null}` - valid
{:.alert.alert-success}

`{"a": 1, "c": 3}` - invalid (missing property `b`)
{:.alert.alert-danger}

`{"c": 1, "d": 3}` - invalid (missing both `a` and `b` properties)
{:.alert.alert-danger}

### dependencies

An object is valid against this keyword if it mets all dependencies
specified by this keyword value. The value of this keyword must be an object,
where property values can be:

- objects representing valid json schemas, and the whole object must match
the entire schema
- arrays of strings representing property names, then the object must
contain all property names

Only property names (from this keyword value) that are also present
in the object are checked.

```json
{
  "type": "object",
  "dependencies": {
    "a": ["b", "c"],
    "c": {
      "type": "object",
      "properties": {
        "b": {
          "type": "integer"
        }
      }     
    }
  }
}
```

If the object has property `a`, then it must also have `b` and `c`.
If it has `c` then `b` can only be an integer.
{:.blockquote-footer}

`{"c": 1}` - valid (`b` is not required)
{:.alert.alert-success}

`{"c": 1, "b": 4}` - valid
{:.alert.alert-success}

`{"a": 1, "b": 4, "c": 3, "d": true}` - valid
{:.alert.alert-success}

`{"b": "str"}` - valid (no dependencies)
{:.alert.alert-success}

`{"c": 1, "b": "str"}` - invalid (`b` must be an integer)
{:.alert.alert-danger}

`{"a": 1, "b": "str"}` - invalid (`c` is not present)
{:.alert.alert-danger}

### minProperties

An object is valid against this keyword if the number of properties it contains
is greater then, or equal to, the value of this keyword. The value of this
keyword must be a non-negative integer. Using `0` as a value has no effect.

```json
{
  "type": "object",
  "minProperties": 2
}
```

Object must have at least `2` properties.
{:.blockquote-footer}

`{"a": "a", "b": "b", "c": "c"}` - valid (3 > 2)
{:.alert.alert-success}

`{"a": "a", "b": "b"}` - valid (2 = 2)
{:.alert.alert-success}

`{"a": "a"}` - invalid (1 < 2)
{:.alert.alert-danger}

`{}` - invalid (0 < 2)
{:.alert.alert-danger}

### maxProperties

An object is valid against this keyword if the number of properties it contains
is lower then, or equal to, the value of this keyword. The value of this
keyword must be a non-negative integer. Using `0` as a value means that
the object must be empty (no properties).

```json
{
  "type": "object",
  "maxProperties": 2
}
```

Object can have at most `2` properties.
{:.blockquote-footer}

`{"a": "a", "b": "b"}` - valid (2 = 2)
{:.alert.alert-success}

`{"a": "a"}` - valid (1 < 2)
{:.alert.alert-success}

`{}` - valid (0 < 2)
{:.alert.alert-success}

`{"a": "a", "b": "b", "c": "c"}` - invalid (3 > 2)
{:.alert.alert-danger}

### propertyNames

An object is valid against this keyword if every property name (key) is valid
against the value of this keyword. The value of this keyword must be a valid
json schema (an object or a boolean).

Please note that the value of `propertyNames` (the schema) will always 
test strings.
{:.alert.alert-info}

```json
{
  "type": "object",
  "propertyNames": {
    "type": "string",
    "minLength": 2
  }
}
```

Every property name must have a minimum length of `2`.
{:.blockquote-footer}

`{"prop1": 0, "prop2": "str"}` - valid
{:.alert.alert-success}

`{"prop": null}` - valid
{:.alert.alert-success}

`{}` - valid
{:.alert.alert-success}

`{"prop": 1, "a": 2}` - invalid (length of `"a"` = 1 < 2)
{:.alert.alert-danger}

### patternProperties

An object is valid against this keyword if every property where
a property name (key) matches a regular expression from the value of this keyword,
is also valid against the corresponding schema.
The value of this keyword must an object, where 
the keys must be valid regular expressions and
the corresponding values must be valid json schemas (object or boolean).

```json
{
  "type": "object",
  "patternProperties": {
    "^str-": {
      "type": "string"
    },
    "^int-": {
      "type": "integer"
    }
  }
}
```

Every property name that starts with `str-` must be a string and
every property name that starts with `int-` must be an integer.
{:.blockquote-footer}

`{"str-a": "a"}` - valid
{:.alert.alert-success}

`{"int-i": 2}` - valid
{:.alert.alert-success}

`{"int-i": 2, "str-a": "a", "other": [1, 2]}` - valid (`other` property is not matched)
{:.alert.alert-success}

`{"other": "a"}` - valid (no property was matched)
{:.alert.alert-success}

`{"str-a": "a", "str-b": 2}` - invalid (`str-b` property is integer, not string)
{:.alert.alert-danger}

`{"str-a": "a", "int-b": 2.5}` - invalid (`int-b` property is float, not integer)
{:.alert.alert-danger}


### additionalProperties

An object is valid against this keyword if all _unchecked_ properties are
valid against the schema defined by the value of this keyword.
_Unchecked_ properties are the properties not checked by the `properties` and
`patternProperties` keywords (if a property name is not present in `properties` keyword 
and doesn't match any regular expression defined by `patternProperties` keyword, then
it is considered _unchecked_).
The value of this keyword must be a valid json schema (object or boolean).

To be more concise, if we have _unchecked_ properties:
- if the value of this keyword is `true`, is always valid
- if the value is `false`, is never valid
- if the value contains an object (schema), every property must be valid against that schema.

```json
{
  "type": "object",
  "additionalProperties": {
    "type": "string"
  }
}
```

Every property value of the object must be a string.
{:.blockquote-footer}

`{"a": "a", "b": "str"}` - valid
{:.alert.alert-success}

`{}` - valid (no properties to check)
{:.alert.alert-success}

`{"str-a": "a", "int-b": 2}` - invalid (`int-b` is integer, not string)
{:.alert.alert-danger}


```json
{
  "type": "object",
  "properties": {
    "a": true,
    "b": true  
  },
  "additionalProperties": false
}
```

Object is invalid if contains other properties than `a` and `b`.
{:.blockquote-footer}

`{"a": "a", "b": "str"}` - valid
{:.alert.alert-success}

`{"a": 1}` - valid
{:.alert.alert-success}

`{}` - valid (no properties to check)
{:.alert.alert-success}

`{"a": "a", "c": 2}` - invalid (`c` property is not allowed)
{:.alert.alert-danger}

`{"a": "a", "c": 2, "d": null}` - invalid (`c` and `d` properties are not allowed)
{:.alert.alert-danger}

```json
{
  "type": "object",
  "patternProperties": {
    "^a": true,
    "^b": true  
  },
  "additionalProperties": false
}
```

Object is invalid if property names doesn't start with `a` or `b`.
{:.blockquote-footer}

`{"a": "a", "b": "str"}` - valid
{:.alert.alert-success}

`{"aAA": "a", "bBB": "str"}` - valid
{:.alert.alert-success}

`{"abc": "a"}` - valid
{:.alert.alert-success}

`{}` - valid (no properties to check)
{:.alert.alert-success}

`{"abc": "a", "extra": 2}` - invalid (`extra` starts with `e`)
{:.alert.alert-danger}

`{"abc": "a", "Bcd": 2}` - invalid (`Bcd` starts with `B` not `b`)
{:.alert.alert-danger}

```json
{
  "type": "object",
  "properties": {
    "a": true,
    "b": true
  },
  "patternProperties": {
    "^extra-": {
      "type": "string"
    }
  },
  "additionalProperties": {
    "type": "integer"
  }
}
```

Object is invalid if property names, exluding `a` and `b` and
those starting with `extra-`, are not integers. Also, properties starting with
`extra-` must be strings.
{:.blockquote-footer}

`{"a": "a", "b": "str"}` - valid
{:.alert.alert-success}

`{"a": 1, "extra-a": "yes"}` - valid
{:.alert.alert-success}

`{"a": 1, "extra-a": "yes", "other": 1}` - valid
{:.alert.alert-success}

`{}` - valid (no properties to check)
{:.alert.alert-success}

`{"a": "a", "extra": 3.5, "other": null}` - invalid (`extra` and `other` must be integers)
{:.alert.alert-danger}

`{"Extra-x": "x"}` - invalid (`Extra-x` does not start with `extra-`, so it must be integer)
{:.alert.alert-danger}

