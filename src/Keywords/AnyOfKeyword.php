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

class AnyOfKeyword implements Keyword
{
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
        $errors = [];

        foreach ($this->value as $index => $value) {
            if ($value === true) {
                return null;
            }

            if ($value === false) {
                continue;
            }

            if (is_object($value) && !($value instanceof Schema)) {
                $value = $this->value[$index] = $context->loader()->loadObjectSchema($value);
            }

            if ($error = $value->validate($context)) {
                $errors[] = $error;
                continue;
            }

            return null;
        }

        return $this->error($schema, $context, 'anyOf', 'The data should match at least one schema', [], $errors);
    }
}