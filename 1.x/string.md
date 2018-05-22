---
layout: project
version: 1.x
title: String type
description: php opis json schema validation of string and text containing unicode characters
keywords: opis, php, json, schema, string, text, validation, pattern, regex, mime, base64
---

# String type

The `string` type is used for validating strings/texts containing
Unicode characters.

```json
{
  "type": "string"
}
```

`"some text"` - valid
{:.alert.alert-success}

`""` - valid (empty string)
{:.alert.alert-success}

`12` - invalid (is integer/number)
{:.alert.alert-danger}

`null` - invalid (is null)
{:.alert.alert-danger}

Please note that in order to calculate the length of a string,
Opis Json Schema uses the following libraries/functions, 
depending which one is available on your system: 
[Opis String](//opis.io/string){:target=_blank} 
(recommended, add it with `composer require opis/string`),
[mb_strlen](http://php.net/manual/en/function.mb-strlen.php){:target=_blank}
(you must enable [mb_string](http://php.net/manual/en/book.mbstring.php){:target=_blank} extension for PHP),
[strlen](http://php.net/manual/en/function.strlen.php){:target=_blank} 
(will not always return the correct length of a string with Unicode characters).
{:.alert.alert-info}

## Validation keywords

The following keywords are supported by the `string` type, and evaluated
in the presented order. All keywords are optional.

### minLength

A string is valid against this keyword if its length is greater then, 
or equal to, the value of this keyword. 
Value of this keyword must be a non-negative integer.

```json
{
  "type": "string",
  "minLength": 3
}
```

Valid if contains at least `3` characters.
{:.blockquote-footer}

`"abc"` - valid (length = 3)
{:.alert.alert-success}

`"abcd"` - valid (length > 3)
{:.alert.alert-success}

`"ab"` - invalid (length < 3)
{:.alert.alert-danger}

### maxLength

A string is valid against this keyword if its length is lower then, 
or equal to, the value of this keyword. 
Value of this keyword must be a non-negative integer.

```json
{
  "type": "string",
  "maxLength": 3
}
```

Valid if contains at most `3` characters.
{:.blockquote-footer}

`"ab"` - valid (length < 3)
{:.alert.alert-success}

`""` - valid (length < 3)
{:.alert.alert-success}

`"abcd"` - invalid (length > 3)
{:.alert.alert-danger}

`"abc"` - valid (length = 3)
{:.alert.alert-success}

### pattern

A string is valid against this keyword if it matches the regular expression
specified by the value of this keyword.
Value of this keyword must be a string representing a valid regular
expression.

Please note that the delimiter used by Opis Json Schema is `/` (slash)
and the modifier is `u` ([PCRE_UTF8](http://php.net/manual/en/reference.pcre.pattern.modifiers.php){:target=_blank}).
{:.alert.alert-info}

```json
{
  "type": "string",
  "pattern": "^opis\\/[a-z-]+$"
}
```

Valid if starts with `opis/` and is followed by either `-` (minus sign) or a lower case letter
between `a` and `z`. The rest of the string can be any character.
{:.blockquote-footer}

`"opis/json-schema"` - valid
{:.alert.alert-success}

`"opis/--"` - valid
{:.alert.alert-success}

`"opis"` - invalid
{:.alert.alert-danger}

`"opis/Json-Schema"` - invalid
{:.alert.alert-danger}

For more information about PHP regular expressions, you can read about
- [Pattern Syntax](http://php.net/manual/en/reference.pcre.pattern.syntax.php){:target=_blank}
- [preg_match function](http://php.net/manual/en/function.preg-match.php){:target=_blank}

### contentEncoding

A string is valid against this keyword if it is encoded using the
method indicated by the value of this keyword. 
Value of this keyword must be a string.

Currently, there can only be two values for this keyword
- `binary` - any string is valid
- `base64` - the string must be a valid base64 encoded string

```json
{
  "type": "string",
  "contentEncoding": "base64"
}
```
Valid if contains only characters inside the base64 alphabet.
{:.blockquote-footer}

`"b3Bpcy9qc29uLXNjaGVtYQ=="` - valid (decodes to `"opis/json-schema"`)
{:.alert.alert-success}

`"opis/json-schema"` - invalid (`-` character is not in the base64 alphabet)
{:.alert.alert-danger}

### contentMediaType

A string is valid against this keyword if its content has the media type
(MIME type) indicated by the value of this keyword.
If the `contentEncoding` keyword is also specified, then the decoded content
must have the indicated media type.
Value of this keyword must be a string.

Out of the box, Opis Json Schema comes with the following media types
- `text/plain` - any text
- `application/json` - json encoded string

If you want to add new media types (MIME types), please read about [Media Types](media-types.html).

```json
{
  "type": "string",
  "contentMediaType": "application/json"
}
```
Valid if the string contains valid JSON syntax.
{:.blockquote-footer}

`"{\"a\": 1}"` - valid (json object)
{:.alert.alert-success}

`"[\"a\", \"b\", 2]"` - valid (json array)
{:.alert.alert-success}

`"\"text\""` - valid (json string)
{:.alert.alert-success}

`"null"` - valid (json null)
{:.alert.alert-success}

`"1-2-3"` - invalid
{:.alert.alert-danger}

`"{a: 1}"` - invalid
{:.alert.alert-danger}

`"a = 23"` - invalid
{:.alert.alert-danger}

```json
{
  "type": "string",
  "contentEncoding": "base64",
  "contentMediaType": "application/json"
}
```
Valid if contains only characters inside base64 alphabet, and the base64 decoded
content contains valid JSON syntax.
{:.blockquote-footer}

`"eyJhIjogMX0="` - valid (decodes to `"{\"a\": 1}"` which is a json object)
{:.alert.alert-success}

`"bnVsbA=="` - valid (decodes to `"null"` which is json null)
{:.alert.alert-success}

`"1-2-3"` - invalid (not a base64 encoded string)
{:.alert.alert-danger}

`"e2E6IDF9"` - invalid (decodes to `"{a: 1}"` which is not a json object)
{:.alert.alert-danger}
