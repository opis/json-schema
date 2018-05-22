---
layout: project
version: 1.x
title: Json Schema definitions
description: using opis json schema $ref keyword to reuse internal definitions 
keywords: opis, json, schema, validation, reference, definitions, $ref
---

# Internal definitions

There are cases when you want to reuse validations that are specific only to
that schema document. For example, we have a custom email validator, and
a custom username validator, and we want to apply those validators multiple
times. Outside the schema document these validators aren't useful. 
This can be easily achieved using [`$ref` keyword](ref-keyword.html)
and the [`definitions` keyword](#definitions).

```json
{
  "type": "object",
  "properties": {
    "username": {"$ref": "#/definitions/custom-username"},
    "aliases": {
      "type": "array",
      "items": {"$ref": "#/definitions/custom-username"}
    },
    "primary_email": {"$ref": "#/definitions/custom-email"},
    "other_emails": {
      "type": "array",
      "items": {"$ref": "#/definitions/custom-email"}
    }
  },
  
  "definitions": {
    "custom-username": {
      "type": "string",
      "minLength":3
    },
    "custom-email": {
      "type": "string",
      "format": "email",
      "pattern": "\\.com$"
    }
  }
}
```  

`{"username": "opis", "primary_email": "opis@example.com"}` - valid
{:.alert.alert-success}

`{"aliases": ["opis json schema", "opis the lib"]}` - valid
{:.alert.alert-success}

`{"other_emails": ["opis@example.com", "opis.lib@example.com"]}` - valid
{:.alert.alert-success}

`{"username": "ab", "primary_email": "opis@example.test"}` - invalid
{:.alert.alert-danger}

`{"aliases": ["opis", "ab"]}` - invalid
{:.alert.alert-danger}

`{"other_email": ["opis@example.test"]}` - invalid
{:.alert.alert-danger}

Ok, let's see what happens there. The confusing thing is the value of the
`$ref` keyword, which is something like this `#/definitions/something`.
That's an URI fragment (starts with `#`), and the rest of the string after
the `#` represents a [json pointer](pointers.html). Json pointers are
covered in the [next](pointers.html) chapter, but we still explain
 the behaviour in a few words, using our example.

Consider this json pointer `/definitions/custom-email`. Because the
pointer starts with `/` (slash) we now that we begin at the root of
the schema document. Every substring delimited by a `/` slash, will
be used as property name (key) to descend. In our case we have two
substrings: `definitions` and `custom-email`. 

Descending into `definitions` gives us

```json
{
  "custom-username": {
    "type": "string",
    "minLength":3
  },
  "custom-email": {
    "type": "string",
    "format": "email",
    "pattern": "\\.com$"
  }
}
```

And from here, descending into `custom-email` gives us

```json
{
  "type": "string",
  "format": "email",
  "pattern": "\\.com$"
}
```

Now, this is the value given by our json pointer.

### definitions

This keyword does not directly validate data, but it contains a map
of validation schemas. The value of this keyword can be anything.
This keyword is not required.

## Examples

#### Definition referencing other definition

```json
{
  "type": "object",
  "properties": {
    "name": {
      "type": "string"
    },
    "personal_data": {
      "$ref": "#/definitions/personal"
    }
  },
   
  "definitions": {
    "email": {
      "type": "string",
      "format": "email"    
    },
    "personal": {
      "type": "object",
      "properties": {
        "mail": {
          "$ref": "#/definitions/email"
        }
      }
    }
  }
}
```

`{"name": "John", "personal_data": {"mail": "john@example.com"}}` - valid
{:.alert.alert-success}

`{"name": "John", "personal_data": {"mail": "invalid-email"}}` - invalid
{:.alert.alert-danger}

`{"name": "John", "personal_data": "john@example.com"}` - invalid
{:.alert.alert-danger}

#### Recursive validation

```json
{
  "type": "object",
  "properties": {
    "name": {
      "type": "string"
    },
    "best_friend": {
      "$ref": "#/definitions/friend"
    }
  },
  
  "definitions": {
    "friend": {
      "type": "object",
      "properties": {
        "name": {
          "type": "string"
        },
        "friends": {
          "type": "array",
          "items": {
            "$ref": "#/definitions/friend"
          }
        }
      }
    }
  }
}
```

Valid examples
{:.text-success}

```json
{
  "name": "John",
  "best_friend": {
    "name": "The dog"
  }
}
```

```json
{
  "name": "John",
  "best_friend": {
    "name": "The dog",
    "friends": [
      {
        "name": "The neighbor's dog",
        "friends": [
          {
            "name": "Underdog"
          },
          {
            "name": "Scooby-Doo"
          }
        ]
      }
    ]
  }
}
```

Invalid examples
{:.text-danger}

```json
{
  "name": "John",
  "best_friend": "The dog"
}
```

```json
{
  "name": "John",
  "best_friend": {
    "name": "The dog",
    "friends": ["Underdog", "Scooby-Doo"]
  }
}
```
