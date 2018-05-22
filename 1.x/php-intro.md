---
layout: project
version: 1.x
title: Opis Json Schema PHP API introduction
description: using the opis json schema validation api in php
keywords: opis, json, schema, validation, api, introduction, php
---

# Opis Json Schema PHP API

This intro page is here to help you quickly find relevant information
when using Opis Json Schema. It is designed as a Q&A because we
try to answer you to the expected (incipient) questions that
 appear when using this library.
 
#### How do I install this library?

Simple, through composer

```bash
composer require opis/json-schema
```

#### How do I validate my data?

Please see the [validator object](php-validator.html) page.

#### How to load json schema documents?

Please see the [loader object](php-loader.html) page.

#### How can I add custom filters ($filters keyword)?

Please see the [custom filters](php-filter.html) page.

#### How can I add custom formats (format keyword)?

Please see the [custom formats](php-format.html) page.

#### How can I add custom media types (contentMediaType keyword)?

Please see the [custom media types](php-media-type.html) page.

#### How can I extend the schema to add my own keywords?

There is no easy way to add your own keywords, because
this means that you'll make schema documents that will deviate
from the original intent of json schema, and you'll end up
spending more time to maintain your own version of json schema
(containing custom keywords) than you'll need to extend and improve your app.
Another reason is that not all keywords from json schema are independent (for example, check out
[additionalProperties](object.html#additionalproperties)), which means that you might need data or information from
another keyword. 
And the last reason is performance, the more keywords you have, the more unnecessary checks will be
made, so the slower the validation process will be. 

Of course, we know that the [json schema
draft](http://json-schema.org/){:target=blank} isn't suitable for every validation case, that's why we added
support for [filters](filters.md) where you can add your desired PHP
validation logic, [variables](variables.html) which enables the [URI template](uri-template.html)
support, and [mappers](mappers.html) to allow hardcore schema reuse.
Not to mention that you can create [custom formats](php-format.html) and [media types](php-media-type.html).

In 99.9999% of custom validation cases, [creating a filter](php-filter.html) is enough,
but if you still think that a new keyword is needed please open a [GitHub issue](https://github.com/opis/json-schema/issues){:target=_blank} to get in touch.
 
#### I cannot find the information I need on this site, what can I do?

Well, you can always [create a GitHub issue](https://github.com/opis/json-schema/issues){:target=_blank} explaining your problem.

 