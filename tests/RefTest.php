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

class RefTest extends AbstractTest
{
    /**
     * @inheritDoc
     */
    public function validationsProvider(): array
    {
        $globals = [
            'prefix' => 'valid',
        ];

        return [
            // uris
            ["file:///simple-ref.json", (object)['name' => 'Name'], true],
            ["file:///simple-ref.json", (object)['name' => 'Name', 'age' => 23], true],
            ["file:///simple-ref.json", (object)['name' => 'Name', '/' => 'slash'], true],
            ["file:///simple-ref.json", (object)['name' => 'Name', 'age' => 8.5], false],
            ["file:///simple-ref.json", (object)['name' => 'Name', '/' => 'backslash'], false],
            ["file:///simple-ref.json#age", 23, true],

            // ids
            ["file:///id-ref.json#a-number", 5, true],
            ["file:///id-ref.json#a-number", -5.5, true],
            ["file:///id-ref.json#a-number", "5", false],
            ["file:///id-ref.json#some-string", "str", true],
            ["file:///id-ref.json#some-string", "", true],
            ["file:///id-ref.json#some-string", 5, false],
            ["file:///id-ref.json#level-5", "level-5", true],
            ["file:///id-ref.json#level-5", "level-6", false],
            ["file:///id-ref.json", (object)['direct' => 'direct'], true],
            ["file:///id-ref.json", (object)['direct' => 'deep'], false],
            ["file:///id-ref.json", (object)['deep' => ['deep']], true],
            ["file:///id-ref.json", (object)['deep' => ['direct']], false],

            // pointers
            ["file:///pointer-ref.json#/properties/b/properties/c", 10, true],
            ["file:///pointer-ref.json#/definitions/c", 10, true],
            ["file:///pointer-ref.json#/properties/a", true, true],
            ["file:///pointer-ref.json", (object)[], true],
            ["file:///pointer-ref.json", (object)["a" => true], true],
            ["file:///pointer-ref.json", (object)["b" => (object)["c" => 10]], true],
            ["file:///pointer-ref.json", (object)["a" => true, "b" => (object)["c" => 10]], true],
            ["file:///pointer-ref.json", (object)["a" => 1, "b" => (object)["c" => 10]], false],
            ["file:///pointer-ref.json", (object)["b" => (object)["c" => 5]], false],

            // templates
            ["file:///var-ref.json", (object)["age" => 18, "regionData" => "eu"], true, false, $globals],
            ["file:///var-ref.json", (object)["age" => 22, "regionData" => "us"], true, false, $globals],
            ["file:///var-ref.json", (object)["age" => 17, "regionData" => "eu"], false, false, $globals],
            ["file:///var-ref.json", (object)["age" => 20, "regionData" => "us"], false, false, $globals],
            ["file:///var-ref.json", (object)["age" => 25, "regionData" => "xx"], false, true, $globals],
            ["file:///var-ref.json", (object)["age" => 25.5, "regionData" => "us"], false, false, $globals],
        ];
    }
}