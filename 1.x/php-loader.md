---
layout: project
version: 1.x
title: Json Schema loader by id
description: the opis json schema loader/resolver system
keywords: opis, json, schema, validation, loader, resolver
---

# Loading and resolving json schemas

The loader object must resolve a schema by URI using an implementation
of `\Opis\JsonSchema\ISchemaLoader` interface.

This object is required by the validator only if you are using external references
(for example, the schemas are in two different files).

Currently, there is only one method that needs to be implemented by a loader.

#### loadSchema()

**Arguments**

- `string` $uri - The uri of the document schema

**Returns** `null|\Opis\JsonSchema\ISchema` - the resolved [schema document](php-schema.html) or `null` on failure.

## Existing loaders

Opis Json Schema ships by default with two existing loaders.

### Memory loader

You can use this loader for test.

```php
<?php
$loader = new \Opis\JsonSchema\Loaders\Memory();
$loader->add('{"type": "string"}', 'http://example.com/string.json');
$schema = $loader->loadSchema("http://example.com/string.json");
```

### File loader

You can use this loader to load schemas from filesystem.

```php
<?php
$loader = new \Opis\JsonSchema\Loaders\File("http://example.com/", [
    "/path/to/schemas",
]);
$schema = $loader->loadSchema("http://example.com/string.json");
// Will search the filesystem for /path/to/schemas/string.json
```

## Creating a custom loader

In this example we will create a loader that maps a directory to an URI.
Given the following directories

```text
[user]
   create.json
   update.json 
[resume]
   [hobby]
        item.json
   create.json
   update.json
```

and the `http://example.com/` as base URI, the following documents should be 
available, and should contain the contents of corresponding files

- http://example.com/user/create.json
- http://example.com/user/update.json
- http://example.com/resume/create.json
- http://example.com/resume/update.json
- http://example.com/resume/hobby/item.json

```php
<?php

use Opis\JsonSchema\{
    ISchemaLoader,
    Schema
};

class DirLoader implements ISchemaLoader
{
    /** @var string[] */
    protected $map = [];
    
    /** @var Schema[] */
    protected $loaded = [];
    
    /**
     * @inheritdoc 
     */
    public function loadSchema(string $uri) {
        // Check if already loaded
        if (isset($this->loaded[$uri])) {
            return $this->loaded[$uri];
        }
        
        // Check the mapping
        foreach ($this->map as $prefix => $dir) {
            if (strpos($uri, $prefix) === 0) {
                // We have a match
                $path = substr($uri, strlen($prefix) + 1);
                $path = $dir . '/' . ltrim($path, '/');
                
                if (file_exists($path)) {
                    // Create a schema object
                    $schema = Schema::fromJsonString(file_get_contents($path));
                    // Save it for reuse
                    $this->loaded[$uri] = $schema;
                    
                    return $schema;
                }
            }
        }
        
        // Nothing found
        return null;
    }
    
    /**
     * @param string $dir
     * @param string $uri_prefix
     * @return bool
     */
    public function registerPath(string $dir, string $uri_prefix): bool
    {
        if (!is_dir($dir)) {
            return false;
        }
        
        $uri_prefix = rtrim($uri_prefix, '/');
        $dir = rtrim($dir, '/');
        
        $this->map[$uri_prefix] = $dir;
        
        return true;
    }
}

```

You can use the loader like this

```php
$loader = new DirLoader();

$loader->register('/path/to/dir', 'http://example.com');

// Don't forget to add it to validator
$validator->setLoader($loader);
```
