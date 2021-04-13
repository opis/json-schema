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

class ArrayTypeTest extends AbstractTest
{
    /**
     * @inheritDoc
     */
    public function validationsProvider(): array
    {
        $schema = "file:///types.json#/definitions/array";

        return [
            ["{$schema}/simple", [1, 2, 3], true],
            ["{$schema}/simple", [], true],
            ["{$schema}/simple", ["a" => 1], false],
            ["{$schema}/simple", [2 => 1], false],

            // interval
            ["{$schema}/interval", ["a", "b"], true],
            ["{$schema}/interval", range(1, 10), true],
            ["{$schema}/interval", range(1, 11), false],
            ["{$schema}/interval", ["a"], false],

            // unique
            ["{$schema}/unique", ["a", "b", "c", 1, "1"], true],
            ["{$schema}/unique", [null, 0, false, "", []], true],
            ["{$schema}/unique", [[1], [2], [3]], true],
            ["{$schema}/unique", [1, "a", 1.0], false],
            ["{$schema}/unique", ["A", "a", "A"], false],
            ["{$schema}/unique", [[[[1]]], [[[1.0]]]], false],

            // contains
            ["{$schema}/contains", [1, "2", "you found me", "other"], true],
            ["{$schema}/contains", [1, "2", "you found ME", "other"], false],
            ["{$schema}/contains", [1, "2", "other"], false],
            ["{$schema}/contains", ["key" => "you found me"], false],

            // items object
            ["{$schema}/items_object", [-3, -2.0, "-1", "", 1, "2", 3, "4", 5.0], true],
            ["{$schema}/items_object", [1, 2], true],
            ["{$schema}/items_object", [], true],
            ["{$schema}/items_object", ["a", "b"], true],
            ["{$schema}/items_object", ["a", 1, 5.123], false],
            ["{$schema}/items_object", ["a", 1, null], false],
            ["{$schema}/items_object", ["a", 1, false], false],

            // items array
            ["{$schema}/items_array", [], true, false, null, null, ['2020-12']],
            ["{$schema}/items_array", [-3], true, false, null, null, ['2020-12']],
            ["{$schema}/items_array", [-3, "ok"], true, false, null, null, ['2020-12']],
            ["{$schema}/items_array", [-3, "ok", 4, "t"], true, false, null, null, ['2020-12']],
            ["{$schema}/items_array", [-3.2, "ok", null, false, []], true, false, null, null, ['2020-12']],
            ["{$schema}/items_array", ["a", 3], false, false, null, null, ['2020-12']],

            // additional items
            ["{$schema}/items_additional", [5.5], true, false, null, null, ['2020-12']],
            ["{$schema}/items_additional", [-3, "ok"], true, false, null, null, ['2020-12']],
            ["{$schema}/items_additional", [-3, "ok", null, null, null], true, false, null, null, ['2020-12']],
            ["{$schema}/items_additional", [-3, "ok", 1, null], false, false, null, null, ['2020-12']],
            ["{$schema}/items_additional", [-3, "ok", null, null, -3], false, false, null, null, ['2020-12']],
        ];
    }
}