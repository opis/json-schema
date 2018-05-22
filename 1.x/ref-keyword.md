---
layout: project
version: 1.x
title: Json Schema references ($ref)
description: using opis json schema $ref keyword to reuse schema by references 
keywords: opis, json, schema, validation, reference, $ref
---

# References (reusing schemas)

Remember when we mentioned about the `$id` keyword in the [Json Schema Structure](structure.html#id-keyword)?
Now is time to use that `$id` for something. As we said, a json schema document
can be identified by an unique id. 

Consider that we have two json schema documents:
one validates a custom email address and the other one validates an user which must
have that custom email address. In order to reuse the custom email validator
we make a reference to it by using the `$ref` keyword. Let's see how it will look.

```json
{
  "$id": "http://example.com/custom-email-validator.json#",
  
  "type": "string",
  "format": "email",
  "pattern": "@example\\.test$"
}
```
The custom email validator.
{:.blockquote-footer}

```json
{
  "type": "object",
  "properties": {
    "name": {
      "type": "string",
      "minLength": 2
    },
    "email": {
      "$ref": "http://example.com/custom-email-validator.json#"
    }
  },
  "required": ["name", "email"],
  "additionalProperties": false
}
```
The user validator.
{:.blockquote-footer}

`{"name": "Opis", "email": "opis@example.test"}` - valid
{:.alert.alert-success}

`{"name": "Opis", "email": "opis@example.com"}` - invalid (`pattern` not matched)
{:.alert.alert-danger}

And what happens here is something which produces a result similar to
the following schema

```json
{
  "type": "object",
  "properties": {
    "name": {
      "type": "string",
      "minLength": 2
    },
    "email": {
        "type": "string",
        "format": "email",
        "pattern": "@example\\.test$"
    }
  },
  "required": ["name", "email"],
  "additionalProperties": false
}
```

This is pretty cool, because now you can write and link different schemas.
You can use `$ref` wherever you need, as many times as you need.

This is the first step in schema reusing.

### $ref

An instance is valid against this keyword if is valid against the
schema that points to the location indicated in the value of this keyword.
The value of this keyword must be a string representing an URI, URI reference, 
URI template or a [json pointer](pointers.html). When present, other validation
keywords (except: [`$vars`](variables.html) and [`$map`](mappers.md)),
 placed on the same level will have no effect. 

This keyword can be applied to any instance type.

Here is a simplified overview of the steps performed in order to validate data by using `$ref` keyword.

- Get the value of `$ref`
    - If the value is an URI template and [`$vars` keyword](variables.html) is present,
    replace template's placeholder and use the result as the value
- If the value is a [relative json pointer](pointers.html#relative-pointers)
    1. Get the subschema using the relative pointer by traversing current document
    2. Raise an error and abort if the subschema is not found
- Otherwise
    1. Get the absolute URI, using `$id` as base for the value of `$ref`
    2. Load the schema document having the `$id` equal to the absolute URI (from the previous step) but without fragment (everything after `#` is removed, including `#` itself)
    3. Raise an error and abort if the document cannot be loaded
    4. If the absolute URI (step 1) has a fragment and is an [absolute json pointer](pointers.html#absolute-pointers), apply the pointer
    to the loaded document in order to get the subschema
    5. Raise an error and abort if the subschema is not found   
- Use the resulted subschema to validate the data
    - If the [`$map` keyword](mappers.html) is present, map the data
    using the mapper, and validate the new (mapped) data 
    instead of the original one
