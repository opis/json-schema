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

namespace Opis\JsonSchema\Schemas;

use Opis\JsonSchema\IContext;
use Opis\JsonSchema\Info\ISchemaInfo;
use Opis\JsonSchema\Errors\IValidationError;
use Opis\JsonSchema\Exceptions\ISchemaException;

final class ExceptionSchema extends AbstractSchema
{

    private ISchemaException $exception;

    /**
     * @param ISchemaInfo $info
     * @param ISchemaException $exception
     */
    public function __construct(ISchemaInfo $info, ISchemaException $exception)
    {
        parent::__construct($info);
        $this->exception = $exception;
    }

    /**
     * @inheritDoc
     * @throws ISchemaException
     */
    public function validate(IContext $context): ?IValidationError
    {
        throw $this->exception;
    }
}