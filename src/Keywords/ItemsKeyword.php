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

class ItemsKeyword implements Keyword
{
    use IterableDataValidationTrait;

    /** @var bool|object|Schema|bool[]|object[]|Schema[] */
    protected $value;

    protected int $count = -1;

    /**
     * @param bool|object|Schema|bool[]|object[]|Schema[] $value
     */
    public function __construct($value)
    {
        $this->value = $value;

        if (is_array($value)) {
            $this->count = count($value);
        }
    }

    /**
     * @inheritDoc
     */
    public function validate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        if ($this->value === true) {
            return null;
        }

        $count = count($context->currentData());

        if ($this->value === false) {
            if ($count === 0) {
                return null;
            }

            return $this->error($schema, $context, 'items', 'Array must be empty');
        }

        if ($this->count >= 0) {

            $errors = $this->errorContainer($context->maxErrors());
            $max = min($count, $this->count);

            for ($i = 0; $i < $max; $i++) {
                if ($this->value[$i] === true) {
                    continue;
                }

                if ($this->value[$i] === false) {
                    return $this->error($schema, $context, 'items', "Array item at index {$i} is not allowed", [
                        'index' => $i,
                    ]);
                }

                if (is_object($this->value[$i]) && !($this->value[$i] instanceof Schema)) {
                    $this->value[$i] = $context->loader()->loadObjectSchema($this->value[$i]);
                }

                $context->pushDataPath($i);
                $error = $this->value[$i]->validate($context);
                $context->popDataPath();

                if ($error) {
                    $errors->add($error);
                    if ($errors->isFull()) {
                        break;
                    }
                }
            }

            if ($errors->isEmpty()) {
                return null;
            }

            return $this->error($schema, $context, 'items', 'Array items must match corresponding schemas', [],
                $errors);
        }

        if (is_object($this->value) && !($this->value instanceof Schema)) {
            $this->value = $context->loader()->loadObjectSchema($this->value);
        }

        return $this->validateIterableData($schema, $this->value, $context, $this->indexes(0, $count),
            'items', 'All array items must match schema');
    }

    /**
     * @param int $start
     * @param int $max
     * @return iterable|int[]
     */
    protected function indexes(int $start, int $max): iterable
    {
        for ($i = $start; $i < $max; $i++) {
            yield $i;
        }
    }
}