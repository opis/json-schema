<?php
/* ============================================================================
 * Copyright 2020 Zindex Software
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ============================================================================ */

namespace Opis\JsonSchema;

use SplObjectStorage;
use Opis\JsonSchema\Info\SchemaInfo;
use Opis\JsonSchema\Schemas\LazySchema;
use Opis\JsonSchema\Parsers\SchemaParser;
use Opis\JsonSchema\Resolvers\SchemaResolver;
use Opis\JsonSchema\Exceptions\DuplicateSchemaIdException;

class SchemaLoader
{
    /** @var Schema[]|object[] */
    protected array $uriCache = [];

    protected SplObjectStorage $dataCache;

    protected SchemaParser $parser;

    protected ?SchemaResolver $resolver;

    protected bool $decodeJsonString = false;

    protected ?Uri $base = null;

    /**
     * @param null|SchemaParser $parser
     * @param null|SchemaResolver $resolver
     * @param bool $decodeJsonString
     */
    public function __construct(?SchemaParser $parser = null, ?SchemaResolver $resolver = null, bool $decodeJsonString = true)
    {
        $this->dataCache = new SplObjectStorage();
        $this->parser = $parser ?? new SchemaParser();
        $this->resolver = $resolver;
        $this->decodeJsonString = $decodeJsonString;
    }

    public function baseUri(): ?Uri
    {
        return $this->base;
    }

    public function setBaseUri(?Uri $uri): self
    {
        $this->base = $uri;
        return $this;
    }

    public function parser(): SchemaParser
    {
        return $this->parser;
    }

    public function setParser(SchemaParser $parser): self
    {
        $this->parser = $parser;

        return $this;
    }

    public function resolver(): ?SchemaResolver
    {
        return $this->resolver;
    }

    public function setResolver(?SchemaResolver $resolver): self
    {
        $this->resolver = $resolver;

        return $this;
    }

    /**
     * @param object $data
     * @param null $id
     * @param string|null $draft
     * @return Schema
     */
    public function loadObjectSchema(object $data, $id = null, ?string $draft = null): Schema
    {
        // Check if already loaded
        if ($schema = $this->checkExistingObject($data)) {
            return $schema;
        }

        if (!$id) {
            $id = $this->createSchemaId($data);
        }

        $handle_id = fn (Uri $id): ?Schema => $this->checkExistingUri($id);

        $handle_object = function (object $data, Uri $id, string $draft): ?Schema {
            $this->handleObject($data, $id, null, null, [], $draft, (string)$id);

            return $this->checkExistingObject($data);
        };

        return $this->parser->parseRootSchema($data, Uri::parse($id, true), $handle_id, $handle_object, $draft);
    }

    /**
     * @param bool $data
     * @param null $id
     * @param string|null $draft
     * @return Schema
     */
    public function loadBooleanSchema(bool $data, $id = null, ?string $draft = null): Schema
    {
        if (!$id) {
            $id = $this->createSchemaId($data);
        }

        return $this->parser->parseSchema(new SchemaInfo($data, Uri::parse($id, true), null, null, [], $draft));
    }

    /**
     * @param Uri $uri
     * @return Schema|null
     */
    public function loadSchemaById(Uri $uri): ?Schema
    {
        if (!$uri->isAbsolute()) {
            if ($this->base === null || !$this->base->isAbsolute()) {
                return null;
            }
            $uri = $this->base->resolveRef($uri);
        }

        $fragment = $uri->fragment();
        if ($fragment === null) {
            $uri = Uri::merge($uri, null, true);
            $fragment = '';
        }

        $schema = $this->checkExistingUri($uri);

        if ($schema !== null) {
            return $schema;
        }

        if ($fragment === '') {
            return $this->resolve($uri);
        }

        $root = Uri::merge('#', $uri);

        // Check if already resolved
        if (($schema = $this->checkExistingUri($root)) === null) {
            // Try to resolve
            if (($schema = $this->resolve($root)) === null) {
                // Schema not found
                return null;
            }
        }

        // Resolve json pointer
        if ($fragment !== '' && $schema && $schema->info()->isObject() &&
            ($pointer = JsonPointer::parse($fragment)) && $pointer->isAbsolute()) {
            $object = $pointer->data($schema->info()->data());
            if (is_bool($object)) {
                $schema = $this->loadBooleanSchema($object, $uri, $schema->info()->draft());
            } elseif (is_object($object)) {
                $schema = $this->loadObjectSchema($object, $uri, $schema->info()->draft());
            } else {
                $schema = null;
            }
            if ($schema) {
                $key = $this->cacheKey((string) $uri);
                $this->uriCache[$key] = $schema;
                return $schema;
            }
        }

        // Check fragment
        return $this->checkExistingUri($uri);
    }

    /**
     * Clears internal cache
     */
    public function clearCache(): void
    {
        $this->dataCache->removeAll($this->dataCache);
        $this->uriCache = [];
    }

    /**
     * @param Uri $uri
     * @return null|Schema
     */
    protected function resolve(Uri $uri): ?Schema
    {
        if ($this->resolver === null) {
            return null;
        }

        $data = $this->resolver->resolve($uri);

        if ($this->decodeJsonString && is_string($data)) {
            $data = json_decode($data, false);
        }

        if (is_bool($data)) {
            $this->handleBoolean($data, $uri, null, null, [], $this->parser->defaultDraftVersion(), (string)$uri);

            return $this->checkExistingUri($uri);
        }

        if (is_object($data)) {
            if ($data instanceof Schema) {
                return $data;
            }

            $this->handleObject($data, $uri, null, null, [], $this->parser->defaultDraftVersion(), (string)$uri);

            return $this->checkExistingObject($data);
        }

        return null;
    }

    /**
     * @param object $data
     * @return null|Schema
     */
    protected function checkExistingObject(object $data): ?Schema
    {
        if (!$this->dataCache->contains($data)) {
            return null;
        }

        $schema = $this->dataCache[$data];

        if ($schema instanceof LazySchema) {
            $schema = $schema->schema();
            $this->dataCache[$data] = $schema;
        } elseif (!($schema instanceof Schema)) {
            $schema = null;
        }

        return $schema;
    }

    /**
     * @param Uri $uri
     * @return null|Schema
     */
    protected function checkExistingUri(Uri $uri): ?Schema
    {
        if ($uri->fragment() === null || !$uri->isAbsolute()) {
            return null;
        }

        $key = $this->cacheKey((string)$uri);

        if (!isset($this->uriCache[$key])) {
            return null;
        }

        $schema = $this->uriCache[$key];

        if (!($schema instanceof Schema)) {
            return $this->uriCache[$key] = $this->checkExistingObject($schema);
        }

        if ($schema instanceof LazySchema) {
            $schema = $schema->schema();
            $this->uriCache[$key] = $schema;
        }

        return $schema;
    }

    /**
     * @param bool $data
     * @param Uri|null $id
     * @param Uri|null $base
     * @param Uri|null $root
     * @param array $path
     * @param string $draft
     * @param string $pointer
     */
    protected function handleBoolean(
        bool $data,
        ?Uri $id,
        ?Uri $base,
        ?Uri $root,
        array $path,
        string $draft,
        string $pointer
    )
    {
        $key = $this->cacheKey($pointer);
        if (isset($this->uriCache[$key])) {
            return;
        }

        $this->uriCache[$key] = $this->parser->parseSchema(new SchemaInfo($data, $id, $base, $root, $path, $draft));
    }

    /**
     * @param array $data
     * @param Uri $base
     * @param Uri $root
     * @param array $path
     * @param string $draft
     * @param string $pointer
     */
    protected function handleArray(array $data, Uri $base, Uri $root, array $path, string $draft, string $pointer)
    {
        foreach ($data as $key => $value) {
            if (!is_int($key)) {
                continue;
            }

            if (is_bool($value)) {
                $this->handleBoolean($value, null, $base, $root, array_merge($path, [$key]), $draft,
                    $pointer . '/' . $key);
            } elseif (is_array($value)) {
                $this->handleArray($value, $base, $root, array_merge($path, [$key]), $draft, $pointer . '/' . $key);
            } elseif (is_object($value)) {
                $this->handleObject($value, null, $base, $root, array_merge($path, [$key]), $draft,
                    $pointer . '/' . $key);
            }
        }
    }

    /**
     * @param object $data
     * @param Uri|null $id
     * @param Uri|null $base
     * @param Uri|null $root
     * @param array $path
     * @param string $draft
     * @param string $pointer
     */
    protected function handleObject(
        object $data,
        ?Uri $id,
        ?Uri $base,
        ?Uri $root,
        array $path,
        string $draft,
        string $pointer
    )
    {
        $schema_id = $this->parser->parseId($data);
        $schema_anchor = $this->parser->parseAnchor($data, $draft);
        $draft = $this->parser->parseSchemaDraft($data) ?? $draft;

        if ($schema_id !== null) {
            $id = Uri::merge($schema_id, $base, true);
        } elseif ($schema_anchor !== null) {
            $id = Uri::merge('#' . $schema_anchor, $base, true);
        }

        $lazy = new LazySchema(new SchemaInfo($data, $id, $base, $root, $path, $draft), $this->parser);

        if ($id && $id->isAbsolute()) {
            $key = $this->cacheKey((string)$id);
            if (isset($this->uriCache[$key])) {
                throw new DuplicateSchemaIdException($id, $data);
            }
            $this->uriCache[$key] = $lazy;
        }

        // When $id and $anchor are both present add a reference to the same lazy object
        if ($schema_id !== null && $schema_anchor !== null) {
            $anchor_id = Uri::merge('#' . $schema_anchor, $id, true);
            $key = $this->cacheKey((string)$anchor_id);
            if (isset($this->uriCache[$key])) {
                throw new DuplicateSchemaIdException($anchor_id, $data);
            }
            $this->uriCache[$key] = $lazy;
        }

        $this->dataCache[$data] = $lazy;
        $this->uriCache[$this->cacheKey($pointer)] = $lazy;

        if ($root === null) {
            $root = $id;
        }

        if ($base === null) {
            $base = $id ?? $root;
        } elseif ($id !== null) {
            $base = $id;
        }

        foreach ($data as $key => $value) {
            if (!is_string($key)) {
                continue;
            }
            if (is_bool($value)) {
                $this->handleBoolean($value, null, $base, $root, array_merge($path, [$key]), $draft,
                    $pointer . '/' . JsonPointer::encodePath($key));
            } elseif (is_array($value)) {
                $this->handleArray($value, $base, $root, array_merge($path, [$key]), $draft,
                    $pointer . '/' . JsonPointer::encodePath($key));
            } elseif (is_object($value)) {
                $this->handleObject($value, null, $base, $root, array_merge($path, [$key]), $draft,
                    $pointer . '/' . JsonPointer::encodePath($key));
            }
        }
    }

    /**
     * @param string $path
     * @return string
     */
    protected function cacheKey(string $path): string
    {
        return isset($path[32]) ? md5($path) : $path;
    }

    /**
     * @param bool|object $data
     * @return string
     */
    protected function createSchemaId($data): string
    {
        if (is_bool($data)) {
            $data = $data ? 'true' : 'false';
        } else {
            $data = spl_object_hash($data);
        }

        return "schema:///{$data}.json";
    }
}