<?php
/* ===========================================================================
 * Copyright 2014-2017 The Opis Project
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
    DuplicateSchemaException, InvalidIdException, InvalidSchemaException
};

class Schema implements ISchema
{

    const BASE_ID_PROP = '$_base_id';
    const PATH_PROP = '$_path';
    const VARS_PROP = '$vars';
    const FILTERS_PROP = '$filters';
    const FUNC_NAME = '$func';
    const ID_PROP = '$id';

    const WALK_IGNORE_PROPERTIES = [
        'type', 'default', 'const', 'enum',
        'title', 'description', 'readOnly', 'writeOnly', 'examples',
    ];

    /** @var string */
    protected $id;

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
            $id = uniqid("urn:");
        }
        $id = URI::normalize($id);
        if (substr($id, -1) !== '#') {
            throw new InvalidIdException($id);
        }

        $this->id = $id;

        if (is_object($data)) {
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
        } elseif (!is_object($schema)) {
            return;
        }

        if (isset($schema->{'$ref'}) && is_string($schema->{'$ref'})) {
            // Set the base id
            $schema->{static::BASE_ID_PROP} = $id;
            // Add current path
            $schema->{static::PATH_PROP} = $path;
            // Do not process $ref
            return;
        }

        if (isset($schema->{static::ID_PROP}) && is_string($schema->{static::ID_PROP})) {
            $schema->{static::BASE_ID_PROP} = $id;
            $id = URI::merge($schema->{static::ID_PROP}, $id);
            if (array_key_exists($id, $container)) {
                throw new DuplicateSchemaException($id, $schema, $container);
            }
            $container[$id] = &$schema;
        }

        foreach ($schema as $name => &$value) {
            if (is_string($name)) {
                if (!isset($name[0]) || $name[0] === '$' || in_array($name, static::WALK_IGNORE_PROPERTIES)) {
                    // Skip empty properties, default and $*
                    continue;
                }
            }
            $path[] = $name;
            static::walk($container, $value, $id, $path);
            array_pop($path);
        }
    }

}