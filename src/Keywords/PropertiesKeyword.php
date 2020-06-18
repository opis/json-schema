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
    use CheckedPropertiesTrait;
    use IterableDataValidationTrait;

    protected array $properties;

    /**
     * @param array $properties
     */
    public function __construct(array $properties)
    {
        $this->properties = $properties;
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
        $data = $context->currentData();

        $errors = $this->errorContainer($context->maxErrors());

        foreach ($this->properties as $name => $prop) {
            if (!property_exists($data, $name)) {
                continue;
            }

            $checked[] = $name;

            if ($prop === true) {
                continue;
            }

            if ($prop === false) {
                return $this->error($schema, $context, 'properties', "Property '{$name}' is not allowed", [
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
            }
        }

        if (!$errors->isEmpty()) {
            return $this->error($schema, $context, 'properties', "The properties must match schema", [], $errors);
        }
        unset($errors);

        if ($checked) {
            $this->addCheckedProperties($context, $checked);
        }

        return null;
    }
}