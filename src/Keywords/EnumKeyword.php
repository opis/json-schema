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
    Helper,
    ValidationContext,
    Keyword,
    Schema
};
use Opis\JsonSchema\Errors\ValidationError;

class EnumKeyword implements Keyword
{
    use ErrorTrait;

    protected ?array $enum;

    /**
     * @param array $enum
     */
    public function __construct(array $enum)
    {
        $this->enum = $this->listByType($enum);
    }

    /**
     * @inheritDoc
     */
    public function validate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        $type = $context->currentDataType();
        $data = $context->currentData();

        if (isset($this->enum[$type])) {
            foreach ($this->enum[$type] as $value) {
                if (Helper::equals($value, $data)) {
                    return null;
                }
            }
        }

        return $this->error($schema, $context, 'enum', 'The data should match one item from enum');
    }

    /**
     * @param array $values
     * @return array
     */
    protected function listByType(array $values): array
    {
        $list = [];

        foreach ($values as $value) {
            $type = Helper::getJsonType($value);
            if (!isset($list[$type])) {
                $list[$type] = [];
            }
            $list[$type][] = $value;
        }

        return $list;
    }
}