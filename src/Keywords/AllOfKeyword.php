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

use Opis\JsonSchema\{
    ValidationContext,
    Keyword,
    Schema
};
use Opis\JsonSchema\Errors\ValidationError;

class AllOfKeyword implements Keyword
{
    use OfTrait;
    use ErrorTrait;

    /** @var bool[]|object[] */
    protected array $value;

    /**
     * @param bool[]|object[] $value
     */
    public function __construct(array $value)
    {
        $this->value = $value;
    }

    /**
     * @inheritDoc
     */
    public function validate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        $object = $this->createArrayObject($context);

        $errors = [];

        foreach ($this->value as $index => $value) {
            if ($value === true) {
                continue;
            }

            if ($value === false) {
                $this->addEvaluatedFromArrayObject($object, $context);
                $error = $this->error($schema, $context, 'allOf', 'The data should match all schemas', [
                    'index' => $index,
                ]);
                if ($context->stopAtFirstError()) {
                    return $error;
                }
                $errors[] = $error;
            }

            if (is_object($value) && !($value instanceof Schema)) {
                $value = $this->value[$index] = $context->loader()->loadObjectSchema($value);
            }

            if ($error = $context->validateSchemaWithoutEvaluated($value, null, false, $object)) {
                $this->addEvaluatedFromArrayObject($object, $context);
                $error = $this->error($schema, $context, 'allOf', 'The data should match all schemas', [
                    'index' => $index,
                ], $error);
                if ($context->stopAtFirstError()) {
                    return $error;
                }
                $errors[] = $error;
            }
        }

        if (count($errors) > 0 && !$context->stopAtFirstError()) {
            return $this->error($schema, $context, 'allOf', 'The data should match all schemas', [
                'index' => $index,
            ], $errors);
        }

        $this->addEvaluatedFromArrayObject($object, $context);

        return null;
    }
}
