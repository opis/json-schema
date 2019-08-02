<?php
/* ===========================================================================
 * Copyright 2018 Zindex Software
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

use Opis\JsonSchema\Exception\{
    DuplicateSchemaException, InvalidSchemaDraftException, InvalidSchemaIdException, InvalidSchemaException, SchemaDraftNotSupportedException
};

class Schema implements ISchema
{

    const SCHEMA_REGEX = '~https?://json-schema\.org/draft-(?<draft>\d\d)/schema#?~i';
    const SUPPORTED_DRAFTS = ['06', '07'];
    const BASE_ID_PROP = '$_base_id';
    const PATH_PROP = '$_path';
    const VARS_PROP = '$vars';
    const FILTERS_PROP = '$filters';
    const FUNC_NAME = '$func';
    const ID_PROP = '$id';
    const MAP_PROP = '$map';

    const WALK_IGNORE_PROPERTIES = [
        'const', 'enum',
        self::FILTERS_PROP, self::VARS_PROP, self::MAP_PROP
    ];

    /** @var string */
    protected $id;

    /** @var string */
    protected $draft;

    /** @var array */
    protected $internal = [];

    /**
     * Schema constructor.
     * @param \stdClass|boolean $data
     * @param string|null $id
     */
    public function __construct($data, string $id = null)
    {
        if (is_object($data)) {
            if (property_exists($data, static::ID_PROP)) {
                $id = $data->{static::ID_PROP};
            }
        } elseif (!is_bool($data)) {
            throw new InvalidSchemaException($data);
        }

        if ($id === null) {
            $id = uniqid("json-schema-id:/");
        }
        $id = URI::merge($id, $id);
        if (substr($id, -1) !== '#') {
            throw new InvalidSchemaIdException($id);
        }

        $this->id = $id;

        if (is_object($data)) {
            if (!property_exists($data, '$schema')) {
                $data->{'$schema'} = 'http://json-schema.org/draft-07/schema#';
            }
            elseif (!is_string($data->{'$schema'})) {
                throw new InvalidSchemaDraftException($data);
            }
            if (!preg_match(static::SCHEMA_REGEX, $data->{'$schema'}, $m)) {
                throw new InvalidSchemaDraftException($data);
            }
            $this->draft = $m['draft'];
            unset($m);
            if (!in_array($this->draft, static::SUPPORTED_DRAFTS)) {
                throw new SchemaDraftNotSupportedException($data, $this->draft);
            }
            $data->{static::ID_PROP} = $id;
            static::walk($this->internal, $data, $id);
            if (isset($data->{'$ref'}) && is_string($data->{'$ref'})) {
                $this->internal[$id] = $data;
            }
        } else {
            $this->internal[$id] = $data;
        }
    }

    /**
     * @inheritDoc
     */
    public function id(): string
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function draft(): string
    {
        return $this->draft;
    }

    /**
     * @inheritDoc
     */
    public function resolve(string $id = null)
    {
        if ($id === null) {
            $id = $this->id;
        }
        return $this->internal[$id] ?? null;
    }

    /**
     * @param array $container
     * @param mixed $schema
     * @param string $id
     * @param array $path
     * @throws DuplicateSchemaException
     */
    public static function walk(array &$container, &$schema, string $id, array $path = [])
    {
        if (is_array($schema)) {
            foreach ($schema as $name => &$value) {
                $path[] = $name;
                static::walk($container, $value, $id, $path);
                array_pop($path);
                unset($value);
            }
            return;
        }

        if (!is_object($schema)) {
            return;
        }

        $has_ref = isset($schema->{'$ref'}) && is_string($schema->{'$ref'});

        if (isset($schema->{static::ID_PROP}) && is_string($schema->{static::ID_PROP})) {
            // Set the base id
            $schema->{static::BASE_ID_PROP} = $id;
            // Add current path
            $schema->{static::PATH_PROP} = $path;

            $id = URI::merge($schema->{static::ID_PROP}, $id);
            if (array_key_exists($id, $container)) {
                throw new DuplicateSchemaException($id, $schema, $container);
            }
            $container[$id] = $schema;

            // Do not process $ref
            if ($has_ref) {
                return;
            }
        } elseif ($has_ref) {
            // Set the base id
            $schema->{static::BASE_ID_PROP} = $id;
            // Add current path
            $schema->{static::PATH_PROP} = $path;

            // Do not process $ref
            return;
        }

        unset($has_ref);

        foreach ($schema as $name => &$value) {
            if (is_null($value) || is_scalar($value)) {
                continue;
            }
            if (in_array($name, static::WALK_IGNORE_PROPERTIES)) {
                continue;
            }
            $path[] = $name;
            static::walk($container, $value, $id, $path);
            array_pop($path);
        }
    }

    /**
     * @param string $json
     * @param string|null $id
     * @return Schema
     */
    public static function fromJsonString(string $json, string $id = null): self
    {
        return new self(json_decode($json, false), $id);
    }
}