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

class PropertiesKeyword implements Keyword
{
    use IterableDataValidationTrait;

    protected array $properties;
    protected array $propertyKeys;

    /**
     * @param array $properties
     */
    public function __construct(array $properties)
    {
        $this->properties = $properties;
        $this->propertyKeys = array_keys($properties);
    }

    /**
     * @inheritDoc
     */
    public function validate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        if (!$this->properties) {
            return null;
        }

        $checked = [];
        $evaluated = [];

        $data = $context->currentData();

        $errors = $this->errorContainer($context->maxErrors());

        foreach ($this->properties as $name => $prop) {
            if (!property_exists($data, $name)) {
                continue;
            }

            $checked[] = $name;

            if ($prop === true) {
                $evaluated[] = $name;
                continue;
            }

            if ($prop === false) {
                $context->addEvaluatedProperties($evaluated);
                return $this->error($schema, $context, 'properties', "Property '{property}' is not allowed", [
                    'property' => $name,
                ]);
            }

            if (is_object($prop) && !($prop instanceof Schema)) {
                $prop = $this->properties[$name] = $context->loader()->loadObjectSchema($prop);
            }

            $context->pushDataPath($name);
            $error = $prop->validate($context);
            $context->popDataPath();

            if ($error) {
                $errors->add($error);
                if ($errors->isFull()) {
                    break;
                }
            } else {
                $evaluated[] = $name;
            }
        }

        $context->addEvaluatedProperties($evaluated);

        if (!$errors->isEmpty()) {
            return $this->error($schema, $context, 'properties', "The properties must match schema: {properties}", [
                'properties' => array_values(array_diff($checked, $evaluated))
            ], $errors);
        }
        unset($errors);

        $context->addCheckedProperties($checked);

        return null;
    }
}