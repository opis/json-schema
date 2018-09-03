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

class InvalidSchemaDraftException extends AbstractSchemaException
{

    /** @var stdClass */
    protected $schema;

    /**
     * InvalidSchemaDraftException constructor.
     * @param stdClass $schema
     * @param Throwable|null $previous
     */
    public function __construct(stdClass $schema, Throwable $previous = null)
    {
        $this->schema = $schema;
        parent::__construct("Invalid '\$schema' property", 0, $previous);
    }

    /**
     * @return stdClass
     */
    public function schema(): stdClass
    {
        return $this->schema;
    }
}