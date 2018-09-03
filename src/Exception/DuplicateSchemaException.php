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

namespace Opis\JsonSchema\Exception;

use stdClass, Throwable;

class DuplicateSchemaException extends AbstractSchemaException
{

    /** @var string */
    protected $id;

    /** @var stdClass */
    protected $schema;

    /** @var array */
    protected $container;

    /**
     * DuplicateSchemaException constructor.
     * @param string $id
     * @param stdClass $schema
     * @param array $container
     * @param Throwable|null $previous
     */
    public function __construct(string $id, stdClass $schema, array $container = [], Throwable $previous = null)
    {
        $this->id = $id;
        $this->schema = $schema;
        $this->container = $container;
        parent::__construct("Duplicate schema '{$id}'", 0, $previous);
    }

    /**
     * @return string
     */
    public function id(): string
    {
        return $this->id;
    }

    /**
     * @return stdClass
     */
    public function schema(): stdClass
    {
        return $this->schema;
    }

    /**
     * @return array
     */
    public function container(): array
    {
        return $this->container;
    }
}