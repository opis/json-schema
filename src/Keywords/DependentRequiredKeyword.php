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

class DependentRequiredKeyword implements Keyword
{
    use ErrorTrait;

    /** @var string[][] */
    protected array $value;

    /**
     * @param string[][] $value
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

        foreach ($this->value as $name => $value) {
            if (!property_exists($data, $name)) {
                continue;
            }
            foreach ($value as $prop) {
                if (!property_exists($data, $prop)) {
                    return $this->error($schema, $context, 'dependentRequired',
                        "'{$prop}' property is required by '{$name}' property", [
                            'property' => $name,
                            'missing' => $prop,
                        ]);
                }
            }
        }

        return null;
    }
}