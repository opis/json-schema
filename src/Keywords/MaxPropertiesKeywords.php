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

class MaxPropertiesKeywords implements Keyword
{
    use ErrorTrait;

    protected int $count;

    /**
     * @param int $count
     */
    public function __construct(int $count)
    {
        $this->count = $count;
    }

    /**
     * @inheritDoc
     */
    public function validate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        $count = count($context->getObjectProperties());

        if ($count <= $this->count) {
            return null;
        }

        return $this->error($schema, $context, 'maxProperties',
            "Object must have at most {max} properties, {count} found", [
                'max' => $this->count,
                'count' => $count,
            ]);
    }
}