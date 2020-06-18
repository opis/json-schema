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

use Opis\JsonSchema\{ValidationContext, Schema, JsonPointer};
use Opis\JsonSchema\Errors\ValidationError;

class MinItemsDataKeyword extends MinItemsKeyword
{

    protected JsonPointer $value;

    /**
     * @param JsonPointer $value
     */
    public function __construct(JsonPointer $value)
    {
        $this->value = $value;
        parent::__construct(0);
    }

    /**
     * @inheritDoc
     */
    public function validate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        /** @var int $count */
        $count = $this->value->data($context->rootData(), $context->currentDataPath(), $this);

        if ($count === $this || !is_int($count) || $count < 0) {
            return $this->error($schema, $context, 'minItems', 'Invalid $data', [
                'pointer' => (string)$this->value,
            ]);
        }

        $this->count = $count;

        return parent::validate($context, $schema);
    }
}