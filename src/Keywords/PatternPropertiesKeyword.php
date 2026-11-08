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

use ArrayIterator, Traversable;
use Opis\JsonSchema\Errors\ValidationError;
use Opis\JsonSchema\{Helper, ValidationContext, Keyword, Schema};

class PatternPropertiesKeyword implements Keyword
{
    use IterableDataValidationTrait;

    /** @var bool[]|object[] */
    protected array $value;

    /** @var string|null */
    protected ?string $currentPattern;

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

        foreach ($this->walk($context, $props) as $list => $subSchema) {
            if ($subSchema === false) {
                return $this->error($schema, $context, 'patternProperties', "Object properties that match pattern '{pattern}' are not allowed", [
                    'pattern' => $this->currentPattern,
                    'forbidden' => $list,
                ]);
            }

            $subErrors = $this->iterateAndValidate($subSchema, $context, $list);

            if (!$subErrors->isEmpty()) {
                return $this->error($schema, $context, 'patternProperties', "Object properties that match pattern '{pattern}' must also match pattern's schema", [
                    'pattern' => $this->currentPattern,
                ], $subErrors);
            }

            unset($subErrors);
        }

        return null;
    }

    /**
     * @param ValidationContext $context
     * @param array $props
     * @return iterable<array<string>, Schema | false>
     */
    protected function walk(ValidationContext $context, array $props): iterable
    {
        $checked = [];
        $it = new ArrayIterator($this->value);
        try {
            foreach ($it as $pattern => $schema) {
                $list = iterator_to_array($this->matchedProperties($pattern, $props, $checked));
                if (!$list || $schema === true) {
                    // no properties matched or schema is true
                    continue;
                }
                // resolve schema
                if (is_object($schema) && !($schema instanceof Schema)) {
                    $schema = $this->value[$pattern] = $context->loader()->loadObjectSchema($schema);
                }
                // save current pattern
                $this->currentPattern = $pattern;
                yield $list => $schema;
            }
        } finally {
            if ($checked) {
                $context->addEvaluatedProperties(array_keys($checked));
            }
            // consume the rest to properly mark as checked
            $it->next();
            while ($it->valid()) {
                iterator_to_array($this->matchedProperties($it->key(), $props, $checked));
                $it->next();
            }
            if ($checked) {
                $context->addCheckedProperties(array_keys($checked));
            }
            $this->currentPattern = null;
        }
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