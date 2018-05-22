---
layout: project
version: 1.x
title: Filters ($filters)
description: using custom filters in json schema to validate data
keywords: opis, json, schema, filter, $filters, validation
---

# Filters

Json Schema specification contains a lot of keywords to validate data,
but most of them are only for _range check_ (like minimum, maximum, minLength, ...).
So, what happens if you want to check if something exists in a database?
Well, there cannot be such thing in json schema because it requires a lot 
of information (hostname, username, pass, query, ...) and it will be a pain
to debug or reuse the schema, not to mention about security concerns.

That's why we created a way to add PHP logic as filters in json schema, 
by adding a new non-standard keyword named `$filters`.

Custom filters can be expensive, so please note that `$filters` is the
last property checked.
{:.alert.alert-warning}

## General structure

In a json schema document, `$filters` can be: 
a string, an object or an array of strings and objects.

`$filters` keyword support is enabled by default, to disable it use `Opis\JsonSchema\Validator::filtersSupport(false)`.
{:.alert.alert-info}

If your filter doesn't need any arguments (besides the value that is validated)
you can use it like a string.

```json
{
  "$filters": "myFilter"
}
```

If you need to send some arguments to filter use an object,
where `$func` keyword holds the filter name and `$vars` keyword (optional) holds
a map of arguments (see more info about [$vars](variables.html)).

```json
{
  "$filters": {
    "$func": "myFilter",
    "$vars": {
      "arg-name-1": 2,
      "arg-other": "something else" 
    }
  }
}
```

You can even use multiple filters by creating an array.

```json
{
  "$filters": [
    "firstFilter", 
    {
      "$func": "secondFilter",
      "$vars": {
        "var1": 1,
        "var2": "value"
      }
    },
    "lastFilter"
  ]
}
```

Please note that if you use an array of filters and one filter doesn't
validate the data, the remaining filters will not be called.
{:.alert.alert-warning}
