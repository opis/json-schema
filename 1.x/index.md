---
layout: project
version: 1.x
title: About
description: About Opis Json Schema validation in PHP
keywords: opis, json, schema, validation, php, about
---

# About

**Opis Json Schema** is a PHP implementation for [json-schema](http://json-schema.org/){:target=_blank} draft-07 and draft-06.

## Features

- Fast validation
- Support for custom [filters](filters.html)
- Supports relative and absolute [json pointers](pointers.html)
- Support for local and global [variables](variables.html)
- Advanced schema reuse with [mappers](mappers.html)
- Support for custom formats
- Support for custom media types
- Support for default value
- And, of course, all the json schema keywords

## Documentation

On this site you can find documentation about json schema itself and about [the API of this library](php-intro.html).

### Json schema keywords

- Document keywords:
[$schema](structure.html#schema-keyword),
[$id](structure.html#id-keyword)
- Metadata keywords:
[title](structure.html#title),
[description](structure.html#description),
[examples](structure.html#examples),
- General keywords:
[type](generics.html#type),
[const](generics.html#const),
[enum](generics.html#enum),
[format](formats.html),
[default](default-value.html),
[definitions](definitions.html),
[$ref](ref-keyword.html)
- Conditionals: 
[not](conditional-subschemas.html#not), 
[if-then-else](conditional-subschemas.html#if-then-else),
[anyOf](multiple-subschemas.html#anyof), 
[oneOf](multiple-subschemas.html#oneof), 
[allOf](multiple-subschemas.html#allof) 
- String keywords:
[minLength](string.html#minlength),
[maxLength](string.html#maxlength),
[pattern](string.html#pattern),
[contentEncoding](string.html#contentencoding),
[contentMediaType](string.html#contentencoding) 
- Number/Integer keywords:
[minimum](number.html#minimum),
[exclusiveMinimum](number.html#exclusiveminimum),
[maximum](number.html#maximum),
[exclusiveMaximum](number.html#exclusivemaximum),
[multipleOf](number.html#multipleof)
- Object keywords:
[properties](object.html#properties),
[required](object.html#required),
[dependencies](object.html#dependencies),
[minProperties](object.html#minproperties),
[maxProperties](object.html#maxproperties),
[propertyNames](object.html#propertynames),
[patternProperties](object.html#patternproperties),
[additionalProperties](object.html#additionalproperties)
- Array keywords:
[minItems](array.html#minitems),
[maxItems](array.html#maxitems),
[uniqueItems](array.html#uniqueitems),
[contains](array.html#contains),
[items](array.html#items),
[additionalItems](array.html#additionalitems)
- Extra keywords:
[$vars](variables.html),
[$filters](filters.html) (including [$func](filters.html))
[$map](mappers.html) (including [$each](mappers.html#mapping-arrays-using-each))

### Schema structure

- [json types](structure.html#data-types)
- [document structure](structure.html#document-structure)
- [URI templates](uri-template.html)
- [json pointers](pointers.html)

## Installation

This library is available on [Packagist](https://packagist.org/packages/opis/json-schema) and can be installed using [Composer](http://getcomposer.org).

```bash
composer require opis/json-schema
```

## Requirements

* PHP 7 or higher

## License

**Opis Json Schema** library is licensed under the [Apache License, Version 2.0](http://www.apache.org/licenses/LICENSE-2.0). 
