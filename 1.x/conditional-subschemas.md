---
layout: project
version: 1.x
title: Applying subschemas conditionally
description: php opis json schema applying subschemas conditionally
keywords: opis, php, json, schema, conditional, subschema, if then else
---

# Applying subschemas conditionally

Sometimes you need to conditionally apply a subschema or to negate the validation
result.
The following keywords help you do that.

## Validation keywords

The following keywords are supported by any instance type, and are evaluated in
the presented order. All keywords are optional.

### not

An instance is valid against this keyword if is **not** valid against 
 the schema defined by the value of this keyword. 
The value of this keyword must be a valid json schema (object or boolean).

```json
{
  "not": {
    "type": "string"
  }
}
```

Accept anything but strings
{:.blockquote-footer}

`-2.3` - valid
{:.alert.alert-success}

`true` - valid
{:.alert.alert-success}

`null` - valid
{:.alert.alert-success}

`{"a": "test"}` - valid
{:.alert.alert-success}

`[1, 2, 3]` - valid
{:.alert.alert-success}

`"some string"` - invalid
{:.alert.alert-danger}

Please pay attention when using `not`! You can write schemas that never
validate!
{:.alert.alert-warning}

```json
{
  "type": "string",
  "not": {
    "type": "string"
  }
}
```

This is never valid.
{:.blockquote-footer}

### if-then-else

This is a conditional structure containing three keywords: `if`, `then` and `else`.
Every keyword value must be a valid json schema (object or boolean).
If the `if` keyword is not present the `then` and `else` keywords are
ignored, but when the `if` keyword is present at least `then` or `else`
should also be present (both can be at the same time).
The instance is valid against this keyword in one of the following cases:
- the if `keyword` validates the instance and the `then` keyword also validates it
- the if `keyword` doesn't validate the instance but the `else` keyword validates it.

As a best practice, please place these keywords in the same order as defined here and do not
add other keywords between them.
{:.alert.alert-info}

```json
{
  "if": {
    "type": "string"
  },
  "then": {
    "minLength": 3
  },
  "else": {
    "const": 0
  }
}
```

If the instance is a `string` then must have a minimum length of `3`, else
it must be `0`.
{:.blockquote-footer}

`"abc"` - valid (string with length = 3)
{:.alert.alert-success}

`"abcd"` - valid (string with length = 4)
{:.alert.alert-success}

`0` - valid
{:.alert.alert-success}

`-0.0` - valid
{:.alert.alert-success}

`"ab"` - invalid (string with length = 2)
{:.alert.alert-danger}

`1` - invalid (not a string and not 0)
{:.alert.alert-danger}

`["abc"]` - (not a string and not 0)
{:.alert.alert-danger}

```json
{
  "if": {
    "type": "string"
  },
  "then": {
    "minLength": 3
  }
}
```

If the instance is a `string` then must have a minimum length of `3`, else
it is invalid.
{:.blockquote-footer}

`"abc"` - valid (string with length = 3)
{:.alert.alert-success}

`"abcd"` - valid (string with length = 4)
{:.alert.alert-success}

`"ab"` - invalid (string with length = 2)
{:.alert.alert-danger}

`0` - invalid (not a string)
{:.alert.alert-danger}

`["abc"]` - (not a string)
{:.alert.alert-danger}

```json
{
  "if": {
    "type": "string"
  },
  "else": {
    "const": 0
  }
}
```

If the instance is a `string` consider it valid, else
it is valid only when `0`.
{:.blockquote-footer}

`"abc"` - valid (string)
{:.alert.alert-success}

`""` - valid (string)
{:.alert.alert-success}

`0` - valid ()
{:.alert.alert-success}

`-0.0` - valid ()
{:.alert.alert-success}

`1` - invalid (not a string and not 0)
{:.alert.alert-danger}

`["abc"]` - (not a string and not 0)
{:.alert.alert-danger}
