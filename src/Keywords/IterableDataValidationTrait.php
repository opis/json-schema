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

use ArrayObject;
use Opis\JsonSchema\{ValidationContext, Schema};
use Opis\JsonSchema\Errors\{ValidationError, ErrorContainer};

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
     * @param Schema $schema
     * @param ValidationContext $context
     * @param iterable $iterator
     * @param ArrayObject|null $keys
     * @return ErrorContainer
     */
    protected function iterateAndValidate(
        Schema $schema,
        ValidationContext $context,
        iterable $iterator,
        ?ArrayObject $keys = null
    ): ErrorContainer {
        $container = $this->errorContainer($context->maxErrors());

        if ($keys) {
            foreach ($iterator as $key) {
                $context->pushDataPath($key);
                $error = $schema->validate($context);
                $context->popDataPath();

                if ($error) {
                    if (!$container->isFull()) {
                        $container->add($error);
                    }
                } else {
                    $keys[] = $key;
                }
            }
        } else {
            foreach ($iterator as $key) {
                $context->pushDataPath($key);
                $error = $schema->validate($context);
                $context->popDataPath();

                if ($error && $container->add($error)->isFull()) {
                    break;
                }
            }
        }

        return $container;
    }

    /**
     * @param Schema $parentSchema
     * @param Schema $schema
     * @param ValidationContext $context
     * @param iterable $iterator
     * @param string $keyword
     * @param string $message
     * @param array $args
     * @param ArrayObject|null $visited_keys
     * @return ValidationError|null
     */
    protected function validateIterableData(
        Schema $parentSchema,
        Schema $schema,
        ValidationContext $context,
        iterable $iterator,
        string $keyword,
        string $message,
        array $args = [],
        ?ArrayObject $visited_keys = null
    ): ?ValidationError {
        $errors = $this->iterateAndValidate($schema, $context, $iterator, $visited_keys);

        if ($errors->isEmpty()) {
            return null;
        }

        return $this->error($parentSchema, $context, $keyword, $message, $args, $errors);
    }
}