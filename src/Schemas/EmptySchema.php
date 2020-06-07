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
use Opis\JsonSchema\Errors\IValidationError;
use Opis\JsonSchema\Info\ISchemaInfo;
use Opis\JsonSchema\IWrapperKeyword;

final class EmptySchema extends AbstractSchema
{

    protected ?IWrapperKeyword $wrapper;

    /**
     * @inheritDoc
     */
    public function __construct(ISchemaInfo $info, ?IWrapperKeyword $wrapper = null)
    {
        parent::__construct($info);
        $this->wrapper = $wrapper;
    }

    /**
     * @inheritDoc
     */
    public function validate(IContext $context): ?IValidationError
    {
        if (!$this->wrapper) {
            return null;
        }

        $context->pushSharedObject();
        $error = $this->wrapper->validate($context);
        $context->popSharedObject();

        return $error;
    }
}