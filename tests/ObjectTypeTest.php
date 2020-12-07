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

class ObjectTypeTest extends AbstractTest
{
    /**
     * @inheritDoc
     */
    public function validationsProvider(): array
    {
        $schema = "file:///types.json#/definitions/object";

        return [
            ["{$schema}/simple", (object)[], true],
            ["{$schema}/simple", null, false],

            // interval
            ["{$schema}/interval", (object)["p1" => 1, "p2" => 2], true],
            ["{$schema}/interval", (object)["p1" => 1, "p2" => 2, "p3" => 3], true],
            ["{$schema}/interval", (object)["p1" => 1], false],
            ["{$schema}/interval", (object)["p1" => 1, "p2" => 2, "p3" => 3, "p4" => 4], false],

            // required
            ["{$schema}/required", (object)["p1" => 1, "p2" => 2], true],
            ["{$schema}/required", (object)["p1" => 1, "p2" => null, "p3" => 2], true],
            ["{$schema}/required", (object)["p1" => 1], false],

            // props
            ["{$schema}/props", (object)[], true],
            ["{$schema}/props", (object)["p1" => 1], true],
            ["{$schema}/props", (object)["p2" => ""], true],
            ["{$schema}/props", (object)["p1" => 1, "p2" => "str"], true],
            ["{$schema}/props", (object)["p1" => 1, "p2" => "str", "p3" => 2.5], true],
            ["{$schema}/props", (object)["p1" => "str", "p2" => 1], false],
            ["{$schema}/props", (object)["p2" => 1], false],
            ["{$schema}/props", (object)["p1" => "str"], false],

            // props additional
            ["{$schema}/props_additional", (object)[], true],
            ["{$schema}/props_additional", (object)["p1" => 1], true],
            ["{$schema}/props_additional", (object)["p2" => ""], true],
            ["{$schema}/props_additional", (object)["p1" => 1, "p2" => "str"], true],
            ["{$schema}/props_additional", (object)["p1" => 1, "p2" => "str", "p5" => null], true],
            ["{$schema}/props_additional", (object)["other" => null], true],
            ["{$schema}/props_additional", (object)["p1" => 1, "p2" => "str", "other" => false], false],

            // property names
            ["{$schema}/pattern", (object)[], true],
            ["{$schema}/pattern", (object)["prop" => 1], true],
            ["{$schema}/pattern", (object)["p1" => 5], true],
            ["{$schema}/pattern", (object)["p8" => "str"], true],
            ["{$schema}/pattern", (object)["p5" => -3.2, "p6" => "str"], true],
            ["{$schema}/pattern", (object)["p34" => false], true],
            ["{$schema}/pattern", (object)["p3" => "5"], false],
            ["{$schema}/pattern", (object)["p0" => 1], false],
            ["{$schema}/pattern-number", (object)["10" => 1, "20" => 2, "30" => 3], true],
            ["{$schema}/pattern-number", (object)[10 => 1, "20.0" => 2, 30 => 3], true],
            ["{$schema}/pattern-number", (object)[10 => 1.5], false],
            ["{$schema}/pattern-number", (object)["10.5" => 1], true],

            // dep
            ["{$schema}/dep", (object)[], true, false, null, null, ['2019-09']],
            ["{$schema}/dep", (object)['e' => 1, 'a' => [1, 2], 'b' => null], true, false, null, null, ['2019-09']],
            ["{$schema}/dep", (object)['e' => 1, 'a' => [1, 2]], false, false, null, null, ['2019-09']],
            ["{$schema}/dep", (object)['e' => 1, 'a' => 1, 'b' => 2], false, false, null, null, ['2019-09']],
            ["{$schema}/dep", (object)['a' => 1, 'b' => 2], true, false, null, null, ['2019-09']],
            ["{$schema}/dep", (object)['c' => 1, 'd' => 2], true, false, null, null, ['2019-09']],
            ["{$schema}/dep", (object)['b' => 1, 'd' => 2], true, false, null, null, ['2019-09']],
            ["{$schema}/dep", (object)['a' => 'str'], false, false, null, null, ['2019-09']],
            ["{$schema}/dep", (object)['c' => 1], false, false, null, null, ['2019-09']],

            //dep2
            ["{$schema}/dep2", (object)[], true, false, null, null, ['06', '07']],
            ["{$schema}/dep2", (object)['e' => 1, 'a' => [1, 2], 'b' => null], true, false, null, null, ['06', '07']],
            ["{$schema}/dep2", (object)['e' => 1, 'a' => [1, 2]], false, false, null, null, ['06', '07']],
            ["{$schema}/dep2", (object)['e' => 1, 'a' => 1, 'b' => 2], false, false, null, null, ['06', '07']],
            ["{$schema}/dep2", (object)['a' => 1, 'b' => 2], true, false, null, null, ['06', '07']],
            ["{$schema}/dep2", (object)['c' => 1, 'd' => 2], true, false, null, null, ['06', '07']],
            ["{$schema}/dep2", (object)['b' => 1, 'd' => 2], true, false, null, null, ['06', '07']],
            ["{$schema}/dep2", (object)['a' => 'str'], false, false, null, null, ['06', '07']],
            ["{$schema}/dep2", (object)['c' => 1], false, false, null, null, ['06', '07']],

            // names
            ["{$schema}/names", (object)[], true],
            ["{$schema}/names", (object)["ab" => 1, "cde" => 2, "aa" => "x"], true],
            ["{$schema}/names", (object)["a" => 1], false],
            ["{$schema}/names", (object)["abcdef" => 1], false],
        ];
    }
}