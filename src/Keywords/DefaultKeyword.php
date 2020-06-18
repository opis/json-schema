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

use Opis\JsonSchema\{Helper, ValidationContext, Keyword, Schema};
use Opis\JsonSchema\Errors\ValidationError;

class DefaultKeyword implements Keyword
{

    protected array $defaults;

    /**
     * @param array $defaults
     */
    public function __construct(array $defaults)
    {
        $this->defaults = $defaults;
    }

    /**
     * @inheritDoc
     */
    public function validate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        $data = $context->currentData();

        if (is_object($data)) {
            foreach ($this->defaults as $name => $value) {
                if (!property_exists($data, $name)) {
                    $data->{$name} = Helper::cloneValue($value);
                }
            }
        }

        return null;
    }
}