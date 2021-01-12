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

class UnevaluatedPropertiesKeyword implements Keyword
{
    use OfTrait;
    use IterableDataValidationTrait;

    /** @var bool|object|Schema */
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
        $unevaluated = $context->getUnevaluatedProperties();

        if (!$unevaluated) {
            return null;
        }

        if ($this->value === true) {
            $context->addEvaluatedProperties($unevaluated);
            return null;
        }

        if ($this->value === false) {
            return $this->error($schema, $context, 'unevaluatedProperties',
                'Unevaluated object properties not allowed: {properties}', [
                    'properties' => $unevaluated,
                ]);
        }

        if (is_object($this->value) && !($this->value instanceof Schema)) {
            $this->value = $context->loader()->loadObjectSchema($this->value);
        }

        $object = $this->createArrayObject($context);

        $error = $this->validateIterableData($schema, $this->value, $context, $unevaluated,
            'unevaluatedProperties', 'All unevaluated object properties must match schema: {properties}', [
                'properties' => $unevaluated,
            ], $object);


        if ($object && $object->count()) {
            $context->addEvaluatedProperties($object->getArrayCopy());
        }

        return $error;
    }
}