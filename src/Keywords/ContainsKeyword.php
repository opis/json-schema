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

class ContainsKeyword implements Keyword
{
    use ErrorTrait;

    /** @var bool|object */
    protected $value;

    /**
     * @param bool|object $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * @inheritDoc
     */
    public function validate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        $data = $context->currentData();

        if ($this->value === true) {
            if ($data) {
                return null;
            }

            return $this->error($schema, $context, 'contains', 'Array must not be empty');
        }

        if ($this->value === false) {
            return $this->error($schema, $context, 'contains', 'Array must be empty');
        }

        if (is_object($this->value) && !($this->value instanceof Schema)) {
            $this->value = $context->loader()->loadObjectSchema($this->value);
        }

        $errors = [];

        foreach ($data as $key => $item) {
            $context->pushDataPath($key);
            $error = $this->value->validate($context);
            $context->popDataPath();

            if ($error === null) {
                return null;
            }

            $errors[] = $error;
        }

        return $this->error($schema, $context, 'contains', 'At least one array item must match schema', [],
            $errors);
    }
}