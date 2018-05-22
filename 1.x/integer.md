---
layout: project
version: 1.x
title: Integer type
description: php opis json schema validation of integer numbers
keywords: opis, php, json, schema, integer, validation
---

# Integer type

The `integer` type is a subtype of [`number` type](number.html) used
for validating only integer numbers. 

```json
{
  "type": "integer"
}
```

`5` - valid (integer)
{:.alert.alert-success}

`-10` - valid (integer)
{:.alert.alert-success}

`5.0` - valid (integer)
{:.alert.alert-success}

`10.5` - invalid (is float)
{:.alert.alert-danger}

`"123"` - invalid (is string)
{:.alert.alert-danger}

`null` - invalid (is null)
{:.alert.alert-danger}

It was added just to simplify the writing of json schemas, because
in reality it is just syntactic sugar for `number` type having the keyword
`multipleOf` set to `1`. So the following two schemas are equivalent.

```json
{
  "type": "integer"
}
```

```json
{
  "type": "number",
  "multipleOf": 1
}
```

## Validation keywords

The `integer` type supports [all keywords that `number` type supports](number.html#validation-keywords).
