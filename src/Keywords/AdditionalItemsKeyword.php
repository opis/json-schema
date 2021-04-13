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

class AdditionalItemsKeyword implements Keyword
{
    use OfTrait;
    use IterableDataValidationTrait;

    /** @var bool|object|Schema */
    protected $value;

    protected int $index;

    /**
     * @param bool|object $value
     * @param int $startIndex
     */
    public function __construct($value, int $startIndex)
    {
        $this->value = $value;
        $this->index = $startIndex;
    }

    /**
     * @inheritDoc
     */
    public function validate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        if ($this->value === true) {
            $context->markAllAsEvaluatedItems();
            return null;
        }

        $data = $context->currentData();
        $count = count($data);

        if ($this->index >= $count) {
            return null;
        }

        if ($this->value === false) {
            return $this->error($schema, $context, 'additionalItems', 'Array should not have additional items', [
                'index' => $this->index,
            ]);
        }

        if (is_object($this->value) && !($this->value instanceof Schema)) {
            $this->value = $context->loader()->loadObjectSchema($this->value);
        }

        $object = $this->createArrayObject($context);

        $error = $this->validateIterableData($schema, $this->value, $context, $this->indexes($this->index, $count),
            'additionalItems', 'All additional array items must match schema', [], $object);

        if ($object && $object->count()) {
            $context->addEvaluatedItems($object->getArrayCopy());
        }

        return $error;
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