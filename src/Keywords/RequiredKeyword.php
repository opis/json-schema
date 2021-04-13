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

class RequiredKeyword implements Keyword
{
    use ErrorTrait;

    /** @var string[] */
    protected ?array $required;

    /**
     * @param string[] $required
     */
    public function __construct(array $required)
    {
        $this->required = $required;
    }

    /**
     * @inheritDoc
     */
    public function validate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        $data = $context->currentData();
        $max = $context->maxErrors();
        $list = [];

        foreach ($this->required as $name) {
            if (!property_exists($data, $name)) {
                $list[] = $name;
                if (--$max <= 0) {
                    break;
                }
            }
        }

        if (!$list) {
            return null;
        }

        return $this->error($schema, $context, 'required', 'The required properties ({missing}) are missing', [
            'missing' => $list,
        ]);
    }
}