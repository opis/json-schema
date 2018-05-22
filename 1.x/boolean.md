---
layout: project
version: 1.x
title: Boolean type
description: php opis json schema validation of boolean type
keywords: opis, php, json, schema, boolean
---

# Boolean type

The `boolean` type is used to validate only boolean values (`true` or `false`).

```json
{
  "type": "boolean"
}
```

`true` - valid
{:.alert.alert-success}

`false` - valid
{:.alert.alert-success}

`"true"` - invalid (is string)
{:.alert.alert-danger}

`null` - invalid (is null)
{:.alert.alert-danger}

`0` - invalid (is integer/number)
{:.alert.alert-danger}

## Validation keywords

The `boolean` type has no specific validation keywords.
