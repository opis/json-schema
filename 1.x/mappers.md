---
layout: project
version: 1.x
title: Mappers ($map)
description: reusing json schema using data mappers
keywords: opis, json, schema, validation, mapper, $map, reuse
---

# Mappers

While json schema standard looks very flexible and powerfull,
it still lacks in providing reusability. And no, we are not talking
about splitting schema into multiple documents, nor using definitions
for common validations, these are great, but what happens if you need to change your
existing data structure, or when you want to use schemas from 3rd parties
and you have no control over their property names?

Take a look at the following scenario:

Some 3rd party provides a basic user validator

```json
{
    "$id": "standard-user.json",
    "type": "object",
    "properties": {
        "name": {
            "type": "string"
        },
        "birthday": {
            "type": "string",
            "format": "date"
        }
    },
    "required": ["name", "birthday"],
    "additionalProperties": false
}
```


So, starting today, our site user must comply with the above `standard-user.json` schema (because let's say it is a law),
 but the problem is that we already have a schema

```json
{
    "$id": "our-user.json",
    "type": "object",
    "properties": {
        "firstName": {
            "type": "string"
        },
        "lastName": {
            "type": "string"
        },
        "email": {
            "type": "string",
            "format": "email"
        }
    },
    "required": ["firstName", "lastName", "email"],
    "additionalProperties": false
}
```


Now, how can we do this without renaming properties nor copying
validation rules from `standard-user.json` to `our-user.json`?
Not to mention that `our-user.json` doesn't have any information
about birthday but contains additional information (email) and
`standard-user.json` is restricted to name and birthday by `additionalProperties` keyword.

The simplest answer is to map our data structure to
the 3rd party data structure and then validate it. Something like

```json
{
    "firstName": "John",
    "lastName": "Doe",
    "email": "johndoe@example.com"
}
```

to be converted to

```json
{
    "name": "John",
    "birthday": "1970-01-01"
}
```

before beeing sent to `standard-user.json` for validation.

We can do that thanks to a new non-standard keyword named `$map`,
designed for advanced schema reuse.

And to solve our problem we only need to prepend the following
rule to `our-user.json`

```json
{
    "allOf": [
        {
            "$ref": "standard-user.json",
            "$map": {
                "name": {"$ref": "/firstName"},
                "birthday": "1970-01-01"
            }
        }
    ]
}
```

`$map` keyword is enabled by default, to disable it use `Opis\JsonSchema\Validator::mapSupport(false)`.
Also, please note that `$map` will not work if `$vars` support is disabled.
{:.alert.alert-info}

## General structure

In a json schema document, `$map` is evaluated like [$vars](variables.html),
the difference is that `$map` can also be an array (`$vars` can only be an object)
and can only be used in conjunction with `$ref`.

Example for `$map`

```json
{
    "$ref": "some-ref.json",
    "$map": {
        "prop1": 1,
        "prop2": "something",
        "dynamic-prop": {"$ref": "/dynamic"}
    }
}
```

In the above example, before the current data is passed to
`some-ref.json` it is processed by `$map`, so in the end it will
look somthing like

```json
{
    "prop1": 1,
    "prop2": "something",
    "dynamic-prop": "value of /dynamic"
}
```


## Mapping arrays using $each

If you want to map every value of an array you
can use `$each` keyword.

```json
{
    "type": "object",
    "properties": {
        "title": {
            "type": "string"
        },
        "list": {
            "type": "array",
            "items": {
                "type": "object",
                "properties": {
                    "index": {"type": "number"},
                    "name": {"type": "string"}
                }
            }
        }
    },
    "allOf": [
        {
            "$ref": "other-schema.json",
            "$map": {
                "name": {"$ref": "/title"},
                "rows": {
                    "$each": {
                        "id": {"$ref": "0/index"},
                        "title": {"$ref": "0/name"},
                        "weight": {"$ref": "0#"}
                    }
                }
                "hide-title": true
            }
        }
    ]
}
```

Considering data to be

```json
{
    "title": "Some title",
    "list": [
        {"index": 5, "name": "A"},
        {"index": 10, "name": "B"},
        {"index": 8, "name": "C"},
    ]
}
```

the mapped data by `$map` will be

```json
{
    "name": "Some title",
    "rows": [
        {"id": 5, "title": "A", "weight": 0},
        {"id": 10, "title": "B", "weight": 1},
        {"id": 8, "title": "C", "weight": 2}
    ],
    "hide-title": true
}
```

## Complex example

Here is a more complex example using two base schemas `user` and `user-permissions` from a 3rd party,
 for our `extended-user` schema.

User schema (3rd party, cannot be changed)

```json
{
    "$id": "user",
    "type": "object",
    "properties": {
        "name": {"type": "string"},
        "active": {"type": "boolean"},
        "required": ["name", "active"]
    },
    "allOf": [
        {"$comment": "And other validations for user..."}
    ],
    "additionalProperties": false
}
```


User permission schema (3rd party, cannot be changed)

```json
{
    "$id": "user-permissions",
    "type": "object",
    "properties": {
        "realm": {
            "type": "string"
        },
        "permissions": {
            "type": "array",
            "items": {
                "type": "object",
                "properties": {
                    "name": {"type": "string"},
                    "enabled": {"type": "boolean"}
                },
                "required": ["name", "enabled"],
                "additionalProperties": false,
                "allOf": [
                    {"$comment": "And other validations for permission..."}
                ]
            }
        }
    },
    "required": ["realm", "permissions"],
    "additionalProperties": false
}
```


Our extended user schema (using `$map` to comply with the 3rd party schemas)

```json
{
    "$id": "extended-user",
    "type": "object",
    "properties": {
        "first-name": {"type": "string"},
        "last-name": {"type": "string"},
        "is-admin": {"type": "boolean"},
        "admin-permissions": {
            "type": "array",
            "items": {
                "enum": ["create", "read", "update", "delete"]
            }
        }
    },
    "required": ["first-name", "last-name", "is-admin", "admin-permissions"],
    "additionalProperties": false,
    "allOf": [
        {
            "$ref": "user",
            "$map": {
                "name": {"$ref": "0/last-name"},
                "active": true
            }
        },
        {
            "$ref": "user-permissions",
            "$map": {
                "realm": "administration",
                "permissions": {
                    "$ref": "0/admin-permissions",
                    "$each": {
                        "name": {"$ref": "0"},
                        "enabled": {"$ref": "2/is-admin"}
                    }
                }
            }
        }
    ]
}
```

So if the data for `extended-user` schema is

```json
{
    "first-name": "Json-Schema",
    "last-name": "Opis",
    "is-admin": true,
    "admin-permissions": ["create", "delete"]
}
```

the mapped data provided to `user` schema (first item of allOf) will be

```json
{
    "name": "Opis",
    "active": true
}
```

and the mapped data provided to `user-permissions` schema (second item of allOf) will be

```json
{
    "realm": "administration",
    "permissions": [
        {
            "name": "create",
            "enabled": true
        },
        {
            "name": "delete",
            "enabled": true
        }
    ]
}
```

Now we are compliant with both 3rd party schemas without changing 
our initial data structure.