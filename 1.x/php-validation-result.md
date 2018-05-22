---
layout: project
version: 1.x
title: Opis Json Schema ValidationResult Object
description: the opis json schema validation result object and errors
keywords: opis, json, schema, validation, validator, result, error
---

# Validation result

Every time you validate something, you'll get as a result an object
of class `\Opis\JsonSchema\ValidationResult`. This object can tell you
if the data passed or not the validation, and in the later case it provides you
the list of errors that occurred.

## ValidationResult methods

#### isValid()

Checks if the data passed validation (return `true` is so).

**Returns** `boolean`

#### hasErrors()

The opposite of [isValid()](#isvalid).

**Returns** `boolean`

#### getErrors()

Get all errors that occurred during validation. An empty array
is returned if there were no errors.

**Returns** `array|\Opis\JsonSchema\ValidationError[]`

#### getFirstError()

Get the first error that occurred, or `null` if there were no errors.

**Returns** `null|\Opis\JsonSchema\ValidationError`

#### totalErrors()

Get the total number of occurred errors and sub-errors.

**Returns** `int`

## ValidationError object

The class `\Opis\JsonSchema\ValidationError` holds all the information
about an error that occurred during the validation of keywords.

#### keyword()

Keyword that generated the error.

**Returns** `string`

#### keywordArgs()

Extra error details set be the keyword itself.

**Returns** `array`

#### schema()

The schema that tried to validate the data.

**Returns** `boolean|stdClass`

#### data()

The invalid data that caused the error.

**Returns** `mixed`

#### dataPointer()

A path to the data that caused the error.

**Returns** `array|string[]|int[]`

#### subErrors()

The list of sub-errors.

**Returns** `array|self[]`

#### subErrorsCount()

The number of sub-errors.

**Returns** `int`

## Validation exceptions

Below is a list of exceptions that can be thrown during a validation.

All exception classes are on the `\Opis\JsonSchema\Exception` namespace,
and all extend `\Opis\JsonSchema\Exception\AbstractSchemaException` class.

For more information please check the source code.

#### DuplicateSchemaException

Schema contains duplicates for [`$id` keyword](structure.html#id-keyword) (after resolving to base).

#### FilterNotFoundException

[Filter](filters.html) doesn't exists or wasn't registered.

#### InvalidJsonPointerException

The [json pointer](pointers.html) is not valid.

#### InvalidSchemaDraftException

[`$schema` keyword](structure.html#schema-keyword) is invalid.

#### InvalidSchemaException

[Schema document](structure.html#document-structure) is not a boolean nor an object.

#### SchemaDraftNotSupportedException

The [draft version](structure.html#schema-keyword) is not supported.

#### SchemaNotFoundException

Schema document cannot be [loaded](php-loader.html).

#### SchemaKeywordException

Some keyword contains invalid value.



