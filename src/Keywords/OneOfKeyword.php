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
    IContext,
    IKeyword,
    ISchema
};
use Opis\JsonSchema\Errors\IValidationError;

class OneOfKeyword implements IKeyword
{
    use ErrorTrait;

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
    public function validate(IContext $context, ISchema $schema): ?IValidationError
    {
        $count = 0;
        $matchedIndex = -1;

        foreach ($this->value as $index => $value) {
            if ($value === false) {
                continue;
            }

            if ($value === true) {
                if (++$count > 1) {
                    return $this->error($schema, $context, 'oneOf', 'The data should match exactly one schema', [
                        'matched' => [$matchedIndex, $index],
                    ]);
                }

                $matchedIndex = $index;
                continue;
            }

            if (is_object($value) && !($value instanceof ISchema)) {
                $value = $this->value[$index] = $context->loader()->loadObjectSchema($value);
            }

            if (!$value->validate($context)) {
                if (++$count > 1) {
                    return $this->error($schema, $context, 'oneOf', 'The data should match exactly one schema', [
                        'matched' => [$matchedIndex, $index],
                    ]);
                }
                $matchedIndex = $index;
            }
        }

        if ($count === 1) {
            return null;
        }

        return $this->error($schema, $context, 'oneOf', 'The data should match exactly one schema', [
            'matched' => [],
        ]);
    }
}