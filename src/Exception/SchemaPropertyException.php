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

namespace Opis\JsonSchema\Exception;

use RuntimeException, stdClass, Throwable;

class SchemaPropertyException extends RuntimeException
{

    /** @var string */
    protected $property;

    /** @var stdClass */
    protected $schema;

    /** @var mixed */
    protected $value;

    /**
     * SchemaPropertyException constructor.
     * @param stdClass $schema
     * @param string $property
     * @param $value
     * @param string $message
     * @param Throwable|null $previous
     */
    public function __construct(stdClass $schema, string $property, $value, string $message, Throwable $previous = null)
    {
        $this->schema = $schema;
        $this->property = $property;
        $this->value = $value;
        parent::__construct($message, 0, $previous);
    }

    /**
     * @return stdClass
     */
    public function schema(): stdClass
    {
        return $this->schema;
    }

    /**
     * @return string
     */
    public function property(): string
    {
        return $this->property;
    }

    /**
     * @return mixed
     */
    public function value()
    {
        return $this->value;
    }
}