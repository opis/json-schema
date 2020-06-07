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

use Opis\JsonSchema\ISchema;
use Opis\JsonSchema\Info\IDataInfo;

class ValidationError implements IValidationError
{

    protected string $keyword;

    protected ISchema $schema;

    protected IDataInfo $data;

    protected array $args;

    protected string $message;

    /** @var IValidationError[] */
    protected array $subErrors;

    /**
     * @param string $keyword
     * @param ISchema $schema
     * @param IDataInfo $data
     * @param string $message
     * @param array $args
     * @param IValidationError[] $subErrors
     */
    public function __construct(
        string $keyword,
        ISchema $schema,
        IDataInfo $data,
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

    /**
     * @inheritDoc
     */
    public function keyword(): string
    {
        return $this->keyword;
    }

    /**
     * @inheritDoc
     */
    public function schema(): ISchema
    {
        return $this->schema;
    }

    /**
     * @inheritDoc
     */
    public function data(): IDataInfo
    {
        return $this->data;
    }

    /**
     * @inheritDoc
     */
    public function args(): array
    {
        return $this->args;
    }

    /**
     * @inheritDoc
     */
    public function message(): string
    {
        return $this->message;
    }

    /**
     * @inheritDoc
     */
    public function subErrors(): array
    {
        return $this->subErrors;
    }

    public function __toString(): string
    {
        return $this->message;
    }
}