---
layout: project
version: 1.x
title: Null type
description: php opis json schema validation of null type
keywords: opis, php, json, schema, null, validation
---

# Boolean type

The `null` type is used to validate the `null` value.

```json
{
  "type": "null"
}
```

`null` - valid
{:.alert.alert-success}

`""` - invalid (is string)
{:.alert.alert-danger}

`false` - invalid (is boolean)
{:.alert.alert-danger}

`0` - invalid (is integer/number)
{:.alert.alert-danger}

## Validation keywords

The `null` type has no specific validation keywords.
