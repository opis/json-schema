---
layout: project
version: 1.x
title: Creating Opis Json Schema media types (MIME)
description: the opis json schema validation using custom media/mime types
keywords: opis, json, schema, validation, media, mime
---

# Creating media types

A custom media type is an object implementing `\Opis\JsonSchema\IMediaType` interface.
The `validate` method receives two arguments and must return 
a `boolean` (`true` if the `$data` has the specified media type, `false` otherwise).
- `string $data` - the data to check
- `string $type` - the media type

```php
<?php

use Opis\JsonSchema\IMediaType;

class MimeType implements IMediaType
{
    public function validate(string $data, string $type): bool {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        return $type == $finfo->buffer($data);
    }
}
```

## Using media types

Before using the [`contentMediaType` keyword](string.html#contentmediatype) in your schemas, make sure
to register them in a `Opis\JsonSchema\IMediaTypeContainer` object, and pass
that object to [`Opis\JsonSchema\IValidator::setMediaType()`](php-validator.html#setmediatype).
- name: the name you will use in your schemas
- the media type object that implements `Opis\JsonSchema\IMediaType`

```php
<?php

use Opis\JsonSchema\{
    Validator,
    MediaTypeContainer
};

// Create a new FormatContainer
$mediaTypes = new MediaTypeContainer();

// Our mime type checker
$mimeType = new MimeType();

// Register our mime types
$mediaTypes->add("text/html", $mimeType);
$mediaTypes->add("text/xml", $mimeType);

// Create a IValidator
$validator = new Validator();

// Set media types to be used by validator
$validator->setMediaType($mediaTypes);

// Validation ...

```

Here is an example that uses our media types

```json
{
  "type": "string",
  "contentMediaType": "text/html"
}
```

This schema validates `"<html></html>"` but doesn't validate `"some string"` (text/plain).