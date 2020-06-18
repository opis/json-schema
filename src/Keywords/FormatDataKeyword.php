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

use Opis\JsonSchema\{Helper, ValidationContext, Schema, JsonPointer};
use Opis\JsonSchema\Errors\ValidationError;
use Opis\JsonSchema\Resolvers\FormatResolver;

class FormatDataKeyword extends FormatKeyword
{

    protected JsonPointer $value;

    protected FormatResolver $resolver;

    /**
     * @param JsonPointer $value
     * @param FormatResolver $resolver
     */
    public function __construct(JsonPointer $value, FormatResolver $resolver)
    {
        $this->value = $value;
        $this->resolver = $resolver;
        parent::__construct('', []);
    }

    /**
     * @inheritDoc
     */
    public function validate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        $value = $this->value->data($context->rootData(), $context->currentDataPath(), $this);
        if ($value === $this || !is_string($value)) {
            return $this->error($schema, $context, 'format', 'Invalid $data', [
                'pointer' => (string)$this->value,
            ]);
        }

        /** @var string $value */

        $type = $context->currentDataType();

        $types = [
            $type => $this->resolver->resolve($value, $type),
        ];

        if (!$types[$type] && ($super = Helper::getJsonSuperType($type))) {
            $types[$super] = $this->resolver->resolve($value, $super);
            unset($super);
        }

        unset($type);

        $this->name = $value;
        $this->types = $types;
        $ret = parent::validate($context, $schema);
        $this->name = $this->types = null;

        return $ret;
    }
}