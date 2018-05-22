---
layout: project
version: 1.x
title: Formats
description: php opis json schema formats
keywords: opis, php, json, schema, formats, date, time, email
---

# Formats

The `format` keyword performs a semantic validation on data.
The value of this keyword must be a string, representig a format.
The keyword behavior depends on the data type, meaning that
the same format name for a `string` behaves differently on a `number`,
or is missing, because not all data types must implement a format and
usually different data types have different formats.

```json
{
    "format": "date"
}
```

`date` format is available only for strings.
{:.blockquote-footer}

`"1970-01-01"` - valid
{:.alert.alert-success}

`510` - valid (not a string)
{:.alert.alert-success}

`"test"` - invalid (not a date)
{:.alert.alert-danger}


## Provided formats

Opis Json Schema provides the following formats for `string` type.

Please note that formats starting with `idn-` require [PHP intl extension](http://php.net/manual/en/book.intl.php){:target=_blank} 
to work correctly.
{:.alert.alert-info}

### date

A string is valid against this format if it represents a date in
the following format: `YYYY-MM-DD`.

```json
{
    "type": "string",
    "format": "date"
}
```

`"1970-01-01"` - valid
{:.alert.alert-success}

`"Jan. 1st, 1970"` - invalid
{:.alert.alert-danger}

### time

A string is valid against this format if it represents a time in
the following format: `hh:mm:ss.sTZD`.

```json
{
    "type": "string",
    "format": "time"
}
```

`"10:05:08"` - valid
{:.alert.alert-success}

`"10:05:08.5"` - valid
{:.alert.alert-success}

`"10:05:08+01:00"` - valid
{:.alert.alert-success}

`"10:05:08-02:30"` - valid
{:.alert.alert-success}

`"10:05:08Z"` - valid
{:.alert.alert-success}

`"45:60:62"` - invalid
{:.alert.alert-danger}

`"10:05"` - invalid
{:.alert.alert-danger}

`"1 p.m.` - invalid
{:.alert.alert-danger}

### date-time

A string is valid against this format if it represents a date-time in
the following format: `YYYY:MM::DDThh:mm:ss.sTZD`.

```json
{
    "type": "string",
    "format": "date-time"
}
```

`"1970-01-01T10:05:08"` - valid
{:.alert.alert-success}

`"1970-01-01T10:05:08.10"` - valid
{:.alert.alert-success}

`"1970-01-01T10:05:08+01:00"` - valid
{:.alert.alert-success}

`"Jan. 1st, 1970 at 1 p.m."` - invalid
{:.alert.alert-danger}

### regex

A string is valid against this format if it represents a
valid regular expression.

```json
{
    "type": "string",
    "format": "regex"
}
```

`"^[a-z]+$"` - valid
{:.alert.alert-success}

`"a/b"` - invalid (slash is not escaped)
{:.alert.alert-danger}

`"(a"` - invalid (incomplete group)
{:.alert.alert-danger}

### email

A string is valid against this format if it represents a
valid e-mail address format.

```json
{
    "type": "string",
    "format": "email"
}
```

`"john@example.com"` - valid
{:.alert.alert-success}

`"john(at)example.com"` - invalid
{:.alert.alert-danger}

### idn-email

A string is valid against this format if it represents a
valid idn e-mail address format.

```json
{
    "type": "string",
    "format": "idn-email"
}
```

`"실례@실례.테스트"` - valid
{:.alert.alert-success}

`"john@example.com"` - valid
{:.alert.alert-success}


`"1234"` - invalid
{:.alert.alert-danger}

### hostname

A string is valid against this format if it represents a valid
hostname.

```json
{
    "type": "string",
    "format": "hostname"
}
```

`"www.example.com"` - valid
{:.alert.alert-success}

`"xn--4gbwdl.xn--wgbh1c"` - valid
{:.alert.alert-success}

`"not_a_valid_host_name"` - invalid
{:.alert.alert-danger}

### idn-hostname

A string is valid against this format if it represents a valid
IDN hostname.

```json
{
    "type": "string",
    "format": "idn-hostname"
}
```

`"실례.테스트"` - valid
{:.alert.alert-success}

`"〮실례.테스트"` - invalid
{:.alert.alert-danger}

### ipv4

A string is valid against this format if it represents a valid
IPv4 address.

```json
{
    "type": "string",
    "format": "ipv4"
}
```

`"192.168.0.1"` - valid
{:.alert.alert-success}

`"192.168.1.1.1"` - invalid
{:.alert.alert-danger}

### ipv6

A string is valid against this format if it represents a valid
IPv6 address.

```json
{
    "type": "string",
    "format": "ipv6"
}
```

`"::1"` - valid
{:.alert.alert-success}

`"12345::"` - invalid
{:.alert.alert-danger}

### json-pointer

A string is valid against this format if it represents a valid
(absolute) json pointer.

```json
{
    "type": "string",
    "format": "json-pointer"
}
```

`"/a/b/c"` - valid
{:.alert.alert-success}

`"/a/~"` - invalid
{:.alert.alert-danger}

### relative-json-pointer

A string is valid against this format if it represents a valid
relative json pointer.

```json
{
    "type": "string",
    "format": "relative-json-pointer"
}
```

`"0/a/b"` - valid
{:.alert.alert-success}

`"5/a/b#"` - valid
{:.alert.alert-success}

`"2#"` - valid
{:.alert.alert-success}

`"/a/b"` - invalid
{:.alert.alert-danger}

### uri

A string is valid against this format if it represents a valid
uri.

```json
{
    "type": "string",
    "format": "uri"
}
```

`"http://example.com/path?qs=v&qs2[1]=3#fragment"` - valid
{:.alert.alert-success}

`"http://a_example.com"` - invalid
{:.alert.alert-danger}

`"aaa/bbb.html"` - invalid
{:.alert.alert-danger}

### uri-reference

A string is valid against this format if it represents a valid
uri or uri reference.

```json
{
    "type": "string",
    "format": "uri-reference"
}
```

`"aaa/bbb.html"` - valid
{:.alert.alert-success}

`"?a=b"` - valid
{:.alert.alert-success}

`"#fragment"` - valid
{:.alert.alert-success}

`"http://example.com"` - valid
{:.alert.alert-success}

`"http://a_example.com"` - invalid
{:.alert.alert-danger}

### uri-template

A string is valid against this format if it represents a valid
uri template or uri-reference.

```json
{
    "type": "string",
    "format": "uri-template"
}
```

`"/{+file}.html"` - valid
{:.alert.alert-success}

`"http://example.com/dictionary/{term:1}/{term}"` - valid
{:.alert.alert-success}

`"{?q,lang}"` - valid
{:.alert.alert-success}

`"http://a_example.com/file.php{?q,r}"` - invalid
{:.alert.alert-danger}
