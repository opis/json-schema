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

namespace Opis\JsonSchema\Errors;

use Opis\JsonSchema\Schema;
use Opis\JsonSchema\Info\DataInfo;

class ValidationError
{
    protected string $keyword;

    protected Schema $schema;

    protected DataInfo $data;

    protected array $args;

    protected string $message;

    /** @var ValidationError[] */
    protected array $subErrors;

    /**
     * @param string $keyword
     * @param Schema $schema
     * @param DataInfo $data
     * @param string $message
     * @param array $args
     * @param ValidationError[] $subErrors
     */
    public function __construct(
        string $keyword,
        Schema $schema,
        DataInfo $data,
        string $message,
        array $args = [],
        array $subErrors = []
    ) {
        $this->keyword = $keyword;
        $this->schema = $schema;
        $this->data = $data;
        $this->message = $message;
        $this->args = $args;
        $this->subErrors = $subErrors;
    }

    public function keyword(): string
    {
        return $this->keyword;
    }

    public function schema(): Schema
    {
        return $this->schema;
    }

    public function data(): DataInfo
    {
        return $this->data;
    }

    public function args(): array
    {
        return $this->args;
    }

    public function message(): string
    {
        return $this->message;
    }

    public function subErrors(): array
    {
        return $this->subErrors;
    }

    public function __toString(): string
    {
        return $this->message;
    }
}