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

final class ValidationError
{

    /** @var mixed */
    protected $data;

    /** @var array */
    protected $dataPointer;

    /** @var bool|\stdClass */
    protected $schema;

    /** @var string */
    protected $error;

    /** @var array */
    protected $errorArgs;

    /** @var ValidationError[] */
    protected $subErrors;

    /**
     * ValidationError constructor.
     * @param $data
     * @param array $data_pointer
     * @param array $parent_data_pointer
     * @param \stdClass|boolean $schema
     * @param string $error
     * @param array $args
     * @param ValidationError[] $sub_errors
     */
    public function __construct($data, array $data_pointer, array $parent_data_pointer, $schema, string $error, array $args = [], array $sub_errors = [])
    {
        $this->data = $data;
        $this->dataPointer = $parent_data_pointer ? array_merge($parent_data_pointer, $data_pointer) : $data_pointer;
        $this->schema = $schema;
        $this->error = $error;
        $this->errorArgs = $args;
        $this->subErrors = $sub_errors;
    }

    /**
     * @return mixed
     */
    public function data()
    {
        return $this->data;
    }

    /**
     * @return array
     */
    public function dataPointer(): array
    {
        return $this->dataPointer;
    }

    /**
     * @return bool|\stdClass
     */
    public function schema()
    {
        return $this->schema;
    }

    /**
     * @return string
     */
    public function error(): string
    {
        return $this->error;
    }

    /**
     * @return array
     */
    public function errorArgs(): array
    {
        return $this->errorArgs;
    }

    /**
     * @return ValidationError[]
     */
    public function subErrors(): array
    {
        return $this->subErrors;
    }

    /**
     * @return int
     */
    public function subErrorsCount(): int
    {
        return count($this->subErrors);
    }
}