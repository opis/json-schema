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

use Opis\JsonSchema\{IContext, ISchema};
use Opis\JsonSchema\Errors\{IValidationError, ErrorContainer};

trait IterableDataValidationTrait
{
    use ErrorTrait;

    /**
     * @param int $maxErrors
     * @return ErrorContainer
     */
    protected function errorContainer(int $maxErrors = 1): ErrorContainer
    {
        return new ErrorContainer($maxErrors);
    }

    /**
     * @param ISchema $schema
     * @param IContext $context
     * @param iterable $iterator
     * @return ErrorContainer
     */
    protected function iterateAndValidate(ISchema $schema, IContext $context, iterable $iterator): ErrorContainer
    {
        $container = $this->errorContainer($context->maxErrors());

        foreach ($iterator as $key) {
            $context->pushDataPath($key);
            $error = $schema->validate($context);
            $context->popDataPath();

            if ($error && $container->add($error)->isFull()) {
                break;
            }
        }

        return $container;
    }

    /**
     * @param ISchema $parentSchema
     * @param ISchema $schema
     * @param IContext $context
     * @param iterable $iterator
     * @param string $keyword
     * @param string $message
     * @param array $args
     * @return IValidationError|null
     */
    protected function validateIterableData(
        ISchema $parentSchema,
        ISchema $schema,
        IContext $context,
        iterable $iterator,
        string $keyword,
        string $message,
        array $args = []
    ): ?IValidationError
    {
        $errors = $this->iterateAndValidate($schema, $context, $iterator);

        if ($errors->isEmpty()) {
            return null;
        }

        return $this->error($parentSchema, $context, $keyword, $message, $args, $errors);
    }
}