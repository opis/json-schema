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

class DependenciesKeyword implements Keyword
{
    use OfTrait;
    use ErrorTrait;

    /** @var array|object[]|string[][] */
    protected array $value;

    /**
     * @param object[]|string[][] $value
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
        $data = $context->currentData();
        $object = $this->createArrayObject($context);

        foreach ($this->value as $name => $value) {
            if ($value === true || !property_exists($data, $name)) {
                continue;
            }

            if ($value === false) {
                $this->addEvaluatedFromArrayObject($object, $context);
                return $this->error($schema, $context, 'dependencies', "Property '{property}' is not allowed", [
                    'property' => $name,
                ]);
            }

            if (is_array($value)) {
                foreach ($value as $prop) {
                    if (!property_exists($data, $prop)) {
                        $this->addEvaluatedFromArrayObject($object, $context);
                        return $this->error($schema, $context, 'dependencies',
                            "Property '{missing}' property is required by property '{property}'", [
                                'property' => $name,
                                'missing' => $prop,
                            ]);
                    }
                }

                continue;
            }

            if (is_object($value) && !($value instanceof Schema)) {
                $value = $this->value[$name] = $context->loader()->loadObjectSchema($value);
            }

            if ($error = $context->validateSchemaWithoutEvaluated($value, null, false, $object)) {
                $this->addEvaluatedFromArrayObject($object, $context);
                return $this->error($schema, $context, 'dependencies',
                    "The object must match dependency schema defined on property '{property}'", [
                        'property' => $name,
                    ], $error);
            }
        }

        $this->addEvaluatedFromArrayObject($object, $context);

        return null;
    }
}