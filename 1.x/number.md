---
layout: project
version: 1.x
title: Number type
description: php opis json schema validation of numbers, integer and float
keywords: opis, php, json, schema, number, validation, integer, float
---

# Number type

The `number` type is used for validating integer and float values.

```json
{
  "type": "number"
}
```

`5` - valid (integer)
{:.alert.alert-success}

`-10.8` - valid (float)
{:.alert.alert-success}

`"123"` - invalid (is string)
{:.alert.alert-danger}

`null` - invalid (is null)
{:.alert.alert-danger}

## Validation keywords

The following keywords are supported by the `number` type, and evaluated
in the presented order. All keywords are optional.

### minimum

A number is valid against this keyword if is greater than, or equal to, the
value of this keyword. 
Value of this keyword must be a number (integer or float).

```json
{
  "type": "number",
  "minimum": 10.5
}
```
Valid if the number is at least `10.5`.
{:.blockquote-footer}

`11` - valid (greater)
{:.alert.alert-success}

`10.5` - valid (equal)
{:.alert.alert-success}

`10` - invalid (lower)
{:.alert.alert-danger}

`10.49` - invalid (lower)
{:.alert.alert-danger}

### exclusiveMinimum

A number is valid against this keyword if is strictly greater than the
value of this keyword. Value of this keyword must be a number (integer or float)
or a boolean. If this keyword holds a boolean, 
then the `minimum` keyword is required and is used as reference for comparison.

```json
{
  "type": "number",
  "exclusiveMinimum": 10.5
}
```
Valid if the number is greater than `10.5`.
{:.blockquote-footer}

`11` - valid (greater)
{:.alert.alert-success}

`10.6` - valid (greater)
{:.alert.alert-success}

`10.5` - invalid (equal)
{:.alert.alert-danger}

`10` - invalid (lower)
{:.alert.alert-danger}

```json
{
  "type": "number",
  "minimum": 10.5,
  "exclusiveMinimum": true
}
```
Valid if the number is greater than `10.5`.
{:.blockquote-footer}

`11` - valid (greater)
{:.alert.alert-success}

`10.6` - valid (greater)
{:.alert.alert-success}

`10.5` - invalid (equal)
{:.alert.alert-danger}

`10` - invalid (lower)
{:.alert.alert-danger}

### maximum

A number is valid against this keyword if is lower than, or equal to, the
value of this keyword. 
Value of this keyword must be a number (integer or float).

```json
{
  "type": "number",
  "maximum": 10.5
}
```
Valid if the number is at most `10.5`.
{:.blockquote-footer}

`10` - valid (lower)
{:.alert.alert-success}

`10.5` - valid (equal)
{:.alert.alert-success}

`10.6` - invalid (greater)
{:.alert.alert-danger}

`11` - invalid (greater)
{:.alert.alert-danger}

### exclusiveMaximum

A number is valid against this keyword if is strictly lower than the
value of this keyword. Value of this keyword must be a number (integer or float)
or a boolean. If this keyword holds a boolean, 
then the `maximum` keyword is required and is used as reference for comparison.

```json
{
  "type": "number",
  "exclusiveMaximum": 10.5
}
```
Valid if the number is lower than `10.5`.
{:.blockquote-footer}

`10` - valid (lower)
{:.alert.alert-success}

`10.49` - valid (lower)
{:.alert.alert-success}

`10.5` - invalid (equal)
{:.alert.alert-danger}

`11` - invalid (greater)
{:.alert.alert-danger}

```json
{
  "type": "number",
  "maximum": 10.5,
  "exclusiveMaximum": true
}
```
Valid if the number is lower than `10.5`.
{:.blockquote-footer}

`10` - valid (lower)
{:.alert.alert-success}

`10.49` - valid (lower)
{:.alert.alert-success}

`10.5` - invalid (equal)
{:.alert.alert-danger}

`11` - invalid (greater)
{:.alert.alert-danger}

### multipleOf

A number is valid against this keyword if the division between the
number and the the value of this keyword results in an integer.
Value of this keyword must be a strictly positive number (zero is not allowed).

```json
{
  "type": "number",
  "multipleOf": 0.5
}
```
Valid if the number divides `0.5` exactly.
{:.blockquote-footer}

`10` - valid (10 / 0.5 = 20)
{:.alert.alert-success}

`1.5` - valid (1.5 / 0.5 = 3)
{:.alert.alert-success}

`-2` - valid (-2 / 0.5 = -4)
{:.alert.alert-success}

`10.2` - invalid (10.2 / 0.5 = 20.40)
{:.alert.alert-danger}

`-3.6` - invalid (-3.6 / 0.5 = -7.2)
{:.alert.alert-danger}
