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

use Throwable;

class InvalidSchemaException extends AbstractSchemaException
{

    /** @var mixed */
    protected $schema;

    /**
     * InvalidSchemaException constructor.
     * @param $schema
     * @param Throwable|null $previous
     */
    public function __construct($schema, Throwable $previous = null)
    {
        $this->schema = $schema;
        $type = is_object($schema) ? get_class($schema) : gettype($schema);
        parent::__construct("Schema must be an object or a boolean, {$type} given", 0, $previous);
    }

    /**
     * @return mixed
     */
    public function schema()
    {
        return $this->schema;
    }
}