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

namespace Opis\JsonSchema\Keywords;

use Opis\JsonSchema\Info\DataInfo;
use Opis\JsonSchema\{IContext, ISchema};
use Opis\JsonSchema\Errors\{ErrorContainer, IValidationError, ValidationError};

trait ErrorTrait
{
    /**
     * @param ISchema $schema
     * @param IContext $context
     * @param string $keyword
     * @param string $message
     * @param array $args
     * @param ErrorContainer|IValidationError|IValidationError[]|null $errors
     * @return IValidationError
     */
    protected function error(
        ISchema $schema,
        IContext $context,
        string $keyword,
        string $message,
        array $args = [],
        $errors = null
    ): IValidationError
    {
        if ($errors) {
            if ($errors instanceof IValidationError) {
                $errors = [$errors];
            } elseif ($errors instanceof ErrorContainer) {
                $errors = $errors->all();
            }
        }

        return new ValidationError($keyword, $schema, DataInfo::fromContext($context), $message, $args,
            is_array($errors) ? $errors : []);
    }
}