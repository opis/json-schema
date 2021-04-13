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

use Traversable;
use Opis\JsonSchema\Errors\ValidationError;
use Opis\JsonSchema\{Helper, ValidationContext, Keyword, Schema};

class PatternPropertiesKeyword implements Keyword
{
    use IterableDataValidationTrait;

    /** @var bool[]|object[] */
    protected array $value;

    /**
     * @param bool[]|object[] $value
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
        $props = $context->getObjectProperties();

        if (!$props) {
            return null;
        }

        $checked = [];

        foreach ($this->value as $pattern => $value) {
            if ($value === true) {
                iterator_to_array($this->matchedProperties($pattern, $props, $checked));
                continue;
            }

            if ($value === false) {
                $list = iterator_to_array($this->matchedProperties($pattern, $props, $checked));

                if ($list) {
                    if ($context->trackUnevaluated()) {
                        $context->addEvaluatedProperties(array_diff(array_keys($checked), $list));
                    }
                    return $this->error($schema, $context, 'patternProperties', "Object properties that match pattern '{pattern}' are not allowed", [
                        'pattern' => $pattern,
                        'forbidden' => $list,
                    ]);
                }

                unset($list);
                continue;
            }

            if (is_object($value) && !($value instanceof Schema)) {
                $value = $this->value[$pattern] = $context->loader()->loadObjectSchema($value);
            }

            $subErrors = $this->iterateAndValidate($value, $context, $this->matchedProperties($pattern, $props, $checked));

            if (!$subErrors->isEmpty()) {
                if ($context->trackUnevaluated()) {
                    $context->addEvaluatedProperties(array_keys($checked));
                }
                return $this->error($schema, $context, 'patternProperties', "Object properties that match pattern '{pattern}' must also match pattern's schema", [
                    'pattern' => $pattern,
                ], $subErrors);
            }

            unset($subErrors);
        }

        if ($checked) {
            $checked = array_keys($checked);
            $context->addCheckedProperties($checked);
            $context->addEvaluatedProperties($checked);
        }

        return null;
    }

    /**
     * @param string $pattern
     * @param array $props
     * @param array $checked
     * @return Traversable|string[]
     */
    protected function matchedProperties(string $pattern, array $props, array &$checked): Traversable
    {
        $pattern = Helper::patternToRegex($pattern);

        foreach ($props as $prop) {
            if (preg_match($pattern, (string)$prop)) {
                $checked[$prop] = true;
                yield $prop;
            }
        }
    }
}