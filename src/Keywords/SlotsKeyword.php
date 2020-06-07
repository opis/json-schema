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

use Opis\JsonSchema\{IContext, IKeyword, ISchema};
use Opis\JsonSchema\Errors\IValidationError;

class SlotsKeyword implements IKeyword
{
    use ErrorTrait;

    /** @var bool[]|ISchema[]|object[]|string[] */
    protected array $slots;

    /** @var string[] */
    protected array $stack = [];

    /**
     * @param string[]|bool[]|object[]|ISchema[] $slots
     */
    public function __construct(array $slots)
    {
        $this->slots = $slots;
    }

    /**
     * @inheritDoc
     */
    public function validate(IContext $context, ISchema $schema): ?IValidationError
    {
        $newContext = $context->newInstance($context->currentData());

        foreach ($this->slots as $name => $fallback) {
            $slot = $this->resolveSlotSchema($name, $context);

            if ($slot === null) {
                $save = true;
                if (is_string($fallback)) {
                    $save = false;
                    $fallback = $this->resolveSlot($fallback, $context);
                }

                if ($fallback === true) {
                    continue;
                }

                if ($fallback === false) {
                    return $this->error($schema, $context, '$slots', "Required slot '{$name}' is missing", [
                        'slot' => $name,
                    ]);
                }

                if (is_object($fallback) && !($fallback instanceof ISchema)) {
                    $fallback = $context->loader()->loadObjectSchema($fallback);
                    if ($save) {
                        $this->slots[$name] = $fallback;
                    }
                }

                $slot = $fallback;
            }

            if ($error = $slot->validate($newContext)) {
                return $this->error($schema, $context,'$slots', "Schema for slot '{$name}' was not matched", [
                    'slot' => $name,
                ], $error);
            }
        }

        return null;
    }

    /**
     * @param string $name
     * @param IContext $context
     * @return ISchema|null
     */
    protected function resolveSlotSchema(string $name, IContext $context): ?ISchema
    {
        do {
            $slot = $context->slot($name);
        } while ($slot === null && $context = $context->parent());

        return $slot;
    }

    /**
     * @param string $name
     * @param IContext $context
     * @return bool|ISchema
     */
    protected function resolveSlot(string $name, IContext $context)
    {
        $slot = $this->resolveSlotSchema($name, $context);

        if ($slot !== null) {
            return $slot;
        }

        if (!isset($this->slots[$name])) {
            return false;
        }

        $slot = $this->slots[$name];

        if (is_bool($slot)) {
            return $slot;
        }

        if (is_object($slot)) {
            if ($slot instanceof ISchema) {
                return $slot;
            }

            $slot = $context->loader()->loadObjectSchema($slot);
            $this->slots[$name] = $slot;
            return $slot;
        }

        if (!is_string($slot)) {
            // Looks like the slot is missing
            return false;
        }

        if (in_array($slot, $this->stack)) {
            // Recursive
            return false;
        }

        $this->stack[] = $slot;
        $slot = $this->resolveSlot($slot, $context);
        array_pop($this->stack);

        return $slot;
    }
}