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

use Opis\JsonSchema\Errors\ValidationError;
use Opis\JsonSchema\{Keyword, Schema, ValidationContext};

class UnevaluatedItemsKeyword implements Keyword
{
    use OfTrait;
    use IterableDataValidationTrait;

    protected $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * @inheritDoc
     */
    public function validate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        $unevaluated = $context->getUnevaluatedItems();

        if (!$unevaluated) {
            return null;
        }

        if ($this->value === true) {
            $context->addEvaluatedItems($unevaluated);
            return null;
        }

        if ($this->value === false) {
            return $this->error($schema, $context, 'unevaluatedItems',
                'Unevaluated array items are not allowed: {indexes}', [
                    'indexes' => $unevaluated,
                ]);
        }

        if (is_object($this->value) && !($this->value instanceof Schema)) {
            $this->value = $context->loader()->loadObjectSchema($this->value);
        }

        $object = $this->createArrayObject($context);

        $error = $this->validateIterableData($schema, $this->value, $context, $unevaluated,
            'unevaluatedItems', 'All unevaluated array items must match schema: {indexes}', [
                'indexes' => $unevaluated,
            ], $object);

        if ($object && $object->count()) {
            $context->addEvaluatedItems($object->getArrayCopy());
        }

        return $error;
    }
}