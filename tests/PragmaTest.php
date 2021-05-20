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

            // cast integers
            ["{$schema}/testCastInteger", 1.2, true],
            ["{$schema}/testCastInteger", "not_an_integer", true],
            ["{$schema}/testCastInteger", "also not integer", true],
            ["{$schema}/testCastInteger", "1212", true],
            ["{$schema}/testCastInteger", "1", true],
            ["{$schema}/testCastInteger", 1, true],
            ["{$schema}/testCastInteger", 0, true],
            ["{$schema}/testCastInteger", "-123.4567", true],
            ["{$schema}/testCastInteger", null, true],
            // array/object become null
            ["{$schema}/testCastInteger", [], false],
            ["{$schema}/testCastInteger", (object)[], false],

            // pass minimum after cast
            ["{$schema}/testCastIntegerMinimum1", 1.2, true],
            ["{$schema}/testCastIntegerMinimum1", 1, true],

            // fail minimum after cast
            ["{$schema}/testCastIntegerMinimum1", "also not integer", false],
            ["{$schema}/testCastIntegerMinimum1", "0", false],
            ["{$schema}/testCastIntegerMinimum1", null, false],
            ["{$schema}/testCastIntegerMinimum1", "-123.4567", false],
            ["{$schema}/testCastIntegerMinimum1", 0, false],

            // Max integer fail
            ["{$schema}/testCastIntegerMaximum0", "1", false],
            ["{$schema}/testCastIntegerMaximum0", "123.23", false],

            // Pass number cast, no min/max; strings and null become 0
            ["{$schema}/testCastNumber", "not a number", true],
            ["{$schema}/testCastNumber", "also not a number", true],
            ["{$schema}/testCastNumber", null, true],
            ["{$schema}/testCastNumber", 0, true],
            ["{$schema}/testCastNumber", "-123.4567", true],
            // array/object become null
            ["{$schema}/testCastNumber", [], false],
            ["{$schema}/testCastNumber", (object)[], false],

            // pass minimum after cast
            ["{$schema}/testCastNumberMinimum1", 1.2, true],
            ["{$schema}/testCastNumberMinimum1", 1, true],
            ["{$schema}/testCastNumberMinimum1", "1.1", true],

            // fail minimum after cast
            ["{$schema}/testCastNumberMinimum1", "also not number", false],
            ["{$schema}/testCastNumberMinimum1", "0", false],
            ["{$schema}/testCastNumberMinimum1", 0, false],
            ["{$schema}/testCastNumberMinimum1", "-123.4567", false],

            // Max number fail
            ["{$schema}/testCastNumberMaximum0", "1", false],
            ["{$schema}/testCastNumberMaximum0", "123.4567", false],

            ["{$schema}/testCastString", 1, true],
            ["{$schema}/testCastString", 1.2, true],
            ["{$schema}/testCastString", true, true],
            ["{$schema}/testCastString", false, true],
            ["{$schema}/testCastString", null, true],
            // string cannot be cast to array or object, regardless if empty
            ["{$schema}/testCastString", [], false],
            ["{$schema}/testCastString", ["non-empty array"], false],
            ["{$schema}/testCastString", (object)[], false],
            ["{$schema}/testCastString", (object)['a' => 1], false],

            // Everything can be cast to array
            ["{$schema}/testCastArray", 1.2, true],
            ["{$schema}/testCastArray", null, true],
            ["{$schema}/testCastArray", false, true],
            ["{$schema}/testCastArray", true, true],
            ["{$schema}/testCastArray", [1, 2], true],
            ["{$schema}/testCastArray", [1, "4"], true],
            ["{$schema}/testCastArray", (object)["a" => 1, "b" => 2.5, "c" => "123"], true],

            // Objects can only be cast to arrays, everything else fails
            ["{$schema}/testCastObject", ["a" => 1, "b" => 2], true],
            ["{$schema}/testCastObject", 1, false],
            ["{$schema}/testCastObject", 1.2, false],
            ["{$schema}/testCastObject", -1.2, false],
            ["{$schema}/testCastObject", "1", false],
            ["{$schema}/testCastObject", "", false],
            ["{$schema}/testCastObject", true, false],
            ["{$schema}/testCastObject", false, false],
            ["{$schema}/testCastObject", null, false],

            // Boolean casts that give true
            ["{$schema}/testCastBooleanTrue", true, true],
            ["{$schema}/testCastBooleanTrue", 1.0, true],
            ["{$schema}/testCastBooleanTrue", 0.1, true],
            ["{$schema}/testCastBooleanTrue", -0.1, true],
            ["{$schema}/testCastBooleanTrue", "-0", true],
            ["{$schema}/testCastBooleanTrue", ["test"], true],
            ["{$schema}/testCastBooleanTrue", (object)["a" => 1, "b" => 2.5, "c" => "123"], true],

            // Boolean casts that give false
            ["{$schema}/testCastBooleanFalse", false, true],
            ["{$schema}/testCastBooleanFalse", "", true],
            ["{$schema}/testCastBooleanFalse", 0, true],
            ["{$schema}/testCastBooleanFalse", -0, true],
            ["{$schema}/testCastBooleanFalse", null, true],
            ["{$schema}/testCastBooleanFalse", [], true],
            ["{$schema}/testCastBooleanFalse", (object)[], true],

            // Integer, number, string and bool to object => null
            ["{$schema}/testCastObjectNull", 123, true],
            ["{$schema}/testCastObjectNull", 123.123, true],
            ["{$schema}/testCastObjectNull", "string", true],
            ["{$schema}/testCastObjectNull", true, true],
            ["{$schema}/testCastObjectNull", false, true],

            // Array to string, number, integer => null
            ["{$schema}/testCastStringNull", [], true],
            ["{$schema}/testCastNumberNull", [], true],
            ["{$schema}/testCastIntegerNull", [], true],

            // Object to string, number, integer => null
            ["{$schema}/testCastStringNull", (object)[], true],
            ["{$schema}/testCastNumberNull", (object)[], true],
            ["{$schema}/testCastIntegerNull", (object)[], true],

        ];
    }
}
