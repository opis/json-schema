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

class PragmaTest extends AbstractTest
{
    /**
     * @inheritDoc
     */
    public function validationsProvider(): array
    {
        $schema = "file:///pragma.json#/definitions";

        return [
            // globals
            ["{$schema}/testGlobals1", 1, true],
            ["{$schema}/testGlobals1", "str", false],
            ["{$schema}/testGlobals2", 1, true, false, ['varType' => 'int']],
            ["{$schema}/testGlobals2", "str", true, false, ['varType' => 'str']],
            // slots
            ["{$schema}/testSlots", 1, true],
            ["{$schema}/testSlots", "str", false],
            // cast
            ["{$schema}/testCastInteger", 1.2, true],
            ["{$schema}/testCastInteger", "-123.4567", true],
            ["{$schema}/testCastInteger", null, true],
            ["{$schema}/testCastInteger", [], false],

            ["{$schema}/testCastNumber", 1.2, true],
            ["{$schema}/testCastNumber", "-123.4567", true],
            ["{$schema}/testCastNumber", null, true],

            ["{$schema}/testCastString", 1.2, true],
            ["{$schema}/testCastString", [], false],
            ["{$schema}/testCastString", (object)['a' => 1], false],

            ["{$schema}/testCastArray", 1.2, true],
            ["{$schema}/testCastArray", [1, 2], true],
            ["{$schema}/testCastArray", [1, "4"], true],
            ["{$schema}/testCastArray", (object)["a" => 1, "b" => 2.5, "c" => "123"], true],

            ["{$schema}/testCastObject", ["a" => 1, "b" => 2], true],
        ];
    }
}