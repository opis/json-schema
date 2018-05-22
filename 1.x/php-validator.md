---
layout: project
version: 1.x
title: Opis Json Schema Validator Object
description: the opis json schema validator object
keywords: opis, json, schema, validation, validator
---

# Validator object

In order to validate your data with json schema, you first must 
create a validator object of type `\Opis\JsonSchema\IValidator`.
Since that is an interface, Opis Json Schema provides an implementation
in class `\Opis\JsonSchema\Validator`, and this is what you will probably
use most of the time.

The validator has a "simple" job: receives as input the json schema and
the data that must be validated against the schema, and returns a
validation result object of type `\Opis\JsonSchema\ValidationResult`.
The validation result can tell you if the data is valid or not against provided schema,
and if is not valid it can provide a list of 
error objects of type `\Opis\JsonSchema\ValidationError`.

The validator object supports three types of validations, depending
on how the schema is provided.

## Validation methods

These methods allow you to validate the data against a schema.

#### dataValidation()

Used to validate data when you have the schema as a 
`\stdClass` object or as a `boolean`.

```php
$schema = (object) [
    'type' => 'string'
];

$result = $validator->dataValidation("abc", $schema);
```

**Arguments**

- `mixed` $data - the data to validate
- `\stdClass|boolean` $schema - json schema
- [`int` $max_errors = `1`] - maximum numbers of validation errors that can occur before data is considered invalid (and method returns)
- [`\Opis\JsonSchema\ISchemaLoader` $loader = `null`] - the loader used to resolve schemas by id

**Returns** `\Opis\JsonSchema\ValidationResult`

#### uriValidation()

Used to validate data when you only have the schema id.

Please note that the schema id is resolved to a document by the [loader](php-loader.html).
{:.alert.alert-info}

```php
$schema = "http://example.com/some/schema.json#";

$result = $validator->uriValidation("the data", $schema);
```

**Arguments**

- `mixed` $data - the data to validate
- `string` $schema - json schema id
- [`int` $max_errors = `1`] - maximum numbers of validation errors that can occur before data is considered invalid (and method returns)
- [`\Opis\JsonSchema\ISchemaLoader` $loader = `null`] - the loader used to resolve schemas by id

**Returns** `\Opis\JsonSchema\ValidationResult`

#### schemaValidation()

Used to validate data when you have the schema as a `\Opis\JsonSchema\ISchema` object.

```php
$schema = \Opis\JsonSchema\Schema::fromJsonString('{"type": "string"}');

$result = $validator->schemaValidation("abc", $schema);
```

**Arguments**

- `mixed` $data - the data to validate
- `\Opis\JsonSchema\ISchema` $schema - json schema
- [`int` $max_errors = `1`] - maximum numbers of validation errors that can occur before data is considered invalid (and method returns)
- [`\Opis\JsonSchema\ISchemaLoader` $loader = `null`] - the loader used to resolve schemas by id

**Returns** `\Opis\JsonSchema\ValidationResult`

## Loader methods

These methods allow you to handle the schema [loader](php-loader.html).

Even if the loader is not required, 
Opis Json Schema comes with some default implementations:
- `\Opis\JsonSchema\Loaders\Memory` - loads schemas from memory 
- `\Opis\JsonSchema\Loaders\File` - loads schemas from filesystem 

#### getLoader()

Get the schema loader used by validator.

**Returns** `null|\Opis\JsonSchema\ISchemaLoader`

#### setLoader()

Sets the schema loader for validator.

**Arguments**

- `null|\Opis\JsonSchema\ISchemaLoader` $loader - the loader object or `null`

**Returns** `self`

## Filter methods

These methods allow you to handle the schema [filters](php-filter.html).

The filters are used by [`$filters` keyword](filters.html).

Opis Json Schema comes with a default implementation for filters container
which can be found in `\Opis\JsonSchema\FilterContainer` class.

#### getFilters()

Get the available filters.

**Returns** `null|\Opis\JsonSchema\IFilterContainer`

#### setFilters()

Sets the available filters.

**Arguments**

- `null|\Opis\JsonSchema\IFilterContainer` $container - the filters object or `null`

**Returns** `self`

## Format methods

These methods allow you to handle the schema [formats](php-format.html).

The formats are used by [`format` keyword](formats.html).

The default format container that ships with Opis Json Schema can be found in
`\Opis\JsonSchema\FormatContainer` class.

#### getFormats()

Get the available formats.

**Returns** `null|\Opis\JsonSchema\IFormatContainer`

#### setFormats()

Sets the available formats.

**Arguments**

- `null|\Opis\JsonSchema\IFormatContainer` $container - the formats object or `null`

**Returns** `self`

## Media type methods

These methods allow you to handle the schema [media types](php-media-type.html).

The types are used by [`contentMediaType` keyword](string.html#contentmediatype).

The default media type container that ships with Opis Json Schema can be found in
`\Opis\JsonSchema\MediaTypeContainer` class.

#### getMediaType()

Get the available media types.

**Returns** `null|\Opis\JsonSchema\IMediaTypeContainer`

#### setMediaType()

Sets the available media types.

**Arguments**

- `null|\Opis\JsonSchema\IMediaTypeContainer` $container - the media types object or `null`

**Returns** `self`

## Helper methods

These methods allow you to change the helper object.
A helper object is an instance of `\Opis\JsonSchema\IValidatorHelper` and
is in charge of determining string lengths, equality checks, type checks,
and others. 

You will probably never use these methods, because Opis Json Schema already
sets a default helper object.

For more information please take a look at the source code.

#### getHelper()

Get the helper used by validator.

**Returns** `\Opis\JsonSchema\IValidatorHelper`

#### setHelper()

Sets the helper for validator.

**Arguments**

- `\Opis\JsonSchema\IValidatorHelper` $helper - the helper object

**Returns** `self`

## Other methods

The following methods are available for `\Opis\JsonSchema\Validator` class.

- setGlobalVars(`array` $vars): `self` - sets the global variables
- getGlobalVars(): `array` - get the current global vars
- defaultSupport(`bool` $enable): `self` - enable/disable support for [`default` keyword](default-value.html)
- hasDefaultSupport(): `bool` - indicates support status for [`default` keyword](default-value.html)
- varsSupport(`bool` $enable): `self` - enable/disable support for [`$vars` keyword](variables.html)
- hasVarsSupport(): `bool` - indicates support status for [`$vars` keyword](variables.html)
- filtersSupport(`bool` $enable): `self` - enable/disable support for [`$filters` keyword](filters.html)
- hasFiltersSupport(): `bool` - indicates support status for [`$filters` keyword](filters.html)
- mapSupport(`bool` $enable): `self` - enable/disable support for [`$map` keyword](mappers.html)
- hasMapSupport(): `bool` - indicates support status for [`$map` keyword](mappers.html)




