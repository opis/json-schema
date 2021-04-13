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

class RequiredDataKeyword extends RequiredKeyword
{

    protected JsonPointer $value;

    /** @var callable|null */
    protected $filter;

    /**
     * @param JsonPointer $value
     * @param callable|null $filter
     */
    public function __construct(JsonPointer $value, ?callable $filter = null)
    {
        $this->value = $value;
        $this->filter = $filter;
        parent::__construct([]);
    }

    /**
     * @inheritDoc
     */
    public function validate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        $required = $this->value->data($context->rootData(), $context->currentDataPath(), $this);
        if ($required === $this || !is_array($required) || !$this->requiredPropsAreValid($required)) {
            return $this->error($schema, $context, 'required', 'Invalid $data', [
                'pointer' => (string)$this->value,
            ]);
        }

        $required = array_unique($required);

        if ($this->filter) {
            $required = array_filter($required, $this->filter);
        }

        if (!$required) {
            return null;
        }

        $this->required = $required;
        $ret = parent::validate($context, $schema);
        $this->required = null;

        return $ret;
    }

    /**
     * @param array $props
     * @return bool
     */
    protected function requiredPropsAreValid(array $props): bool
    {
        foreach ($props as $prop) {
            if (!is_string($prop)) {
                return false;
            }
        }

        return true;
    }
}