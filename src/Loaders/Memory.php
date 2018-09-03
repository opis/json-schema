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

namespace Opis\JsonSchema\Loaders;

use Opis\JsonSchema\{
    ISchema, ISchemaLoader, Schema
};

class Memory implements ISchemaLoader
{

    protected $schemas = [];

    /**
     * @inheritDoc
     */
    public function loadSchema(string $uri)
    {
        return $this->schemas[$uri] ?? null;
    }

    /**
     * @param ISchema $schema
     * @return Memory
     */
    public function register(ISchema $schema): self
    {
        $this->schemas[rtrim($schema->id(), '#')] = $schema;
        return $this;
    }

    /**
     * @param ISchema $schema
     * @return Memory
     */
    public function unregister(ISchema $schema): self
    {
        unset($this->schemas[rtrim($schema->id(), '#')]);
        return $this;
    }

    /**
     * @param $data
     * @param string|null $id
     * @return Memory
     */
    public function add($data, string $id = null): self
    {
        if (is_string($data)) {
            $data = json_decode($data, false);
        }
        return $this->register(new Schema($data, $id));
    }

    /**
     * @param string $id
     * @return Memory
     */
    public function remove(string $id): self
    {
        unset($this->schemas[$id]);
        return $this;
    }

}