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
    protected ?int $min = null;
    protected ?int $max = null;

    /**
     * @param bool|object $value
     * @param int|null $min
     * @param int|null $max
     */
    public function __construct($value, ?int $min = null, ?int $max = null)
    {
        $this->value = $value;
        $this->min = $min;
        $this->max = $max;
    }

    /**
     * @inheritDoc
     */
    public function validate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        $data = $context->currentData();
        $count = count($data);

        if ($this->min > $count) {
            return $this->error($schema, $context, 'minContains', 'Array must have at least @min items', [
                'min' => $this->min,
                'count' => $count,
            ]);
        }

        if ($this->value === true) {
            if ($count) {
                if ($this->max !== null && $count > $this->max) {
                    return $this->error($schema, $context, 'maxContains', 'Array must have at most @max items', [
                        'max' => $this->max,
                        'count' => $count,
                    ]);
                }
                return null;
            }

            return $this->error($schema, $context, 'contains', 'Array must not be empty');
        }

        if ($this->value === false) {
            return $this->error($schema, $context, 'contains', 'Any array is invalid');
        }

        if (is_object($this->value) && !($this->value instanceof Schema)) {
            $this->value = $context->loader()->loadObjectSchema($this->value);
        }

        $errors = [];
        $valid = 0;

        foreach ($data as $key => $item) {
            $context->pushDataPath($key);
            $error = $this->value->validate($context);
            $context->popDataPath();

            if ($error === null) {
                $valid++;
                if ($valid < $this->min) {
                    // TODO: ...
                    // next
                    continue;
                }
                if ($this->max !== null && $valid > $this->max) {

                }
                return null;
            }

            $errors[] = $error;
        }

        return $this->error($schema, $context, 'contains', 'At least one array item must match schema', [],
            $errors);
    }
}