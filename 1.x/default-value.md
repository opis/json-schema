---
layout: project
version: 1.x
title: Default value
description: php opis json schema default value
keywords: opis, php, json, schema, validation, default
---

# Default value

You can specify a default value for an item using the `default` keyword.
When a data doesn't have a corresponding value, the value of this keyword
will be used instead to do the validation checks.
This keyword is not mandatory and the value of this keyword can be anything.

```json
{
  "type": "object",
  "properties": {
    "prop1": {
      "type": "string",
      "default": "test"
    }
  }
}
```

`{"prop1": "string"}` - valid
{:.alert.alert-success}

`{}` - valid (the default value for `prop1` is used to validate the property)
{:.alert.alert-success}

`{"prop1": 5}` - invalid (not a string)
{:.alert.alert-danger}


Please pay attention when using `default`! The value of the keyword must pass
the validations!
{:.alert.alert-warning}

```json
{
  "type": "object",
  "properties": {
    "prop1": {
      "type": "string",
      "default": 5
    }
  }
}
```

This will not be valid if `prop1` is missing because 
the default value is not a string.
{:.blockquote-footer}

`{"prop1": "string"}` - valid
{:.alert.alert-success}

`{}` - invalid (default value is not a string)
{:.alert.alert-danger}

`{"prop1": null}` - invalid (null is not a string)
{:.alert.alert-danger}