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

namespace Opis\JsonSchema\Test;

class BasicTest extends AbstractTest
{
    public function validationsProvider(): array
    {
        $schema = "file:///basic.json#/definitions";

        return [
            // Types
            ["{$schema}/types", 10, true],
            ["{$schema}/types", -5.0, true],
            ["{$schema}/types", [], true],
            ["{$schema}/types", [1, 2], true],
            ["{$schema}/types", [1, "a"], true],
            ["{$schema}/types", "str", false],
            ["{$schema}/types", 2.1, false],
            ["{$schema}/types", null, false],
            ["{$schema}/types", true, false],
            ["{$schema}/types", (object)[], false],

            // Constants
            ["{$schema}/constant", 10, true],
            ["{$schema}/constant", "a", false],

            // Enums
            ["{$schema}/enumeration", "b", true],
            ["{$schema}/enumeration", 10, false],

            // Booleans
            ["{$schema}/false", "always fail", false],
            ["{$schema}/true", (object)['x' => 'always valid'], true],

            // Empty schema
            ["{$schema}/empty", "for empty schema", true],

            // Default value
            ["{$schema}/def", (object)["a" => "aaa"], true],
            ["{$schema}/def", (object)[], true],
            ["{$schema}/def", (object)["a" => 5], false],

            // Not
            ["{$schema}/cond/negation", 10, true],
            ["{$schema}/cond/negation", "x", true],
            ["{$schema}/cond/negation", "a", false],
            ["{$schema}/cond/negation", "b", false],

            // if-then
            ["{$schema}/cond/if-then", 10, true, false, null, null, ['06']],
            ["{$schema}/cond/if-then", 10.5, true, false, null, null, ['06']],
            ["{$schema}/cond/if-then", "str", true, false, null, null, ['06']],
            ["{$schema}/cond/if-then", 5, false, false, null, null, ['06']],

            // if-else
            ["{$schema}/cond/if-else", 10, true, false, null, null, ['06']],
            ["{$schema}/cond/if-else", 5, true, false, null, null, ['06']],
            ["{$schema}/cond/if-else", 5.8, true, false, null, null, ['06']],
            ["{$schema}/cond/if-else", "str", false, false, null, null, ['06']],

            // if-then-else
            ["{$schema}/cond/if-then-else", 10, true, false, null, null, ['06']],
            ["{$schema}/cond/if-then-else", 5.8, true, false, null, null, ['06']],
            ["{$schema}/cond/if-then-else", 5, false, false, null, null, ['06']],
            ["{$schema}/cond/if-then-else", 3.2, false, false, null, null, ['06']],

            // allOf
            ["{$schema}/cond/all", 1, true],
            ["{$schema}/cond/all", 1.0, true],
            ["{$schema}/cond/all", 2, false],
            ["{$schema}/cond/all", 3, false],
            ["{$schema}/cond/all", "str", false],

            // anyOf
            ["{$schema}/cond/any", 1, true],
            ["{$schema}/cond/any", "127.0.0.1", true],
            ["{$schema}/cond/any", "x", true],
            ["{$schema}/cond/any", "y", true],
            ["{$schema}/cond/any", 5.5, false],
            ["{$schema}/cond/any", "z", false],

            // oneOf
            ["{$schema}/cond/one", 1, true],
            ["{$schema}/cond/one", -2.0, true],
            ["{$schema}/cond/one", true, true],
            ["{$schema}/cond/one", false, true],
            ["{$schema}/cond/one", [1, 2], true],
            ["{$schema}/cond/one", ["a"], true],
            ["{$schema}/cond/one", [1, 2, "a"], true],
            ["{$schema}/cond/one", "str", false],
            ["{$schema}/cond/one", ["a", "b"], false],
        ];
    }
}