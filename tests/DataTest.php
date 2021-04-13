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

class DataTest extends AbstractTest
{
    /**
     * @inheritDoc
     */
    protected static function parserOptions(): array
    {
        return [
            'allowDataKeyword' => true,
        ];
    }

    /**
     * @inheritDoc
     */
    public function validationsProvider(): array
    {
        $schema = "file:///data.json#/definitions";

        return [
            // const
            ["{$schema}/const", (object)['value' => [1, 2, 3], 'const' => [1, 2, 3]], true],
            ["{$schema}/const", (object)['value' => "abc", 'const' => "abc"], true],
            ["{$schema}/const", (object)['value' => 123, 'const' => "abc"], false],

            // enum
            ["{$schema}/enum", (object)['value' => 'a', 'enum' => ['a', 'b', 1]], true],
            ["{$schema}/enum", (object)['value' => 1, 'enum' => ['a', 'b', 1]], true],
            ["{$schema}/enum", (object)['value' => 123, 'enum' => ['a', 'b', 1]], false],
            ["{$schema}/enum", (object)['value' => 5, 'enum' => 5], false],

            // format
            ["{$schema}/format", (object)['value' => 'test@example.com', 'format' => 'email'], true],
            ["{$schema}/format", (object)['value' => 'test(at)example.com', 'format' => 'email'], false],
            ["{$schema}/format", (object)['value' => '1970-01-01', 'format' => 'date'], true],
            ["{$schema}/format", (object)['value' => '1970-02-31', 'format' => 'date'], false],
            ["{$schema}/format", (object)['value' => '123', 'format' => 123], false],

            // number: minimum & maximum
            ["{$schema}/number-min-max", (object)['value' => 5, 'min' => 1, 'max' => 10], true],
            ["{$schema}/number-min-max", (object)['value' => 1, 'min' => 1, 'max' => 10], true],
            ["{$schema}/number-min-max", (object)['value' => 10, 'min' => 1, 'max' => 10], true],
            ["{$schema}/number-min-max", (object)['value' => 0, 'min' => 1, 'max' => 10], false],
            ["{$schema}/number-min-max", (object)['value' => 12, 'min' => 1, 'max' => 10], false],
            ["{$schema}/number-min-max", (object)['value' => 1, 'min' => [1], 'max' => 10], false],
            ["{$schema}/number-min-max", (object)['value' => 1, 'min' => 1, 'max' => [10]], false],

            // number: exclusive minimum & maximum
            ["{$schema}/number-ex-min-max", (object)['value' => 5, 'min' => 1, 'max' => 10], true],
            ["{$schema}/number-ex-min-max", (object)['value' => 9.99999, 'min' => 1, 'max' => 10], true],
            ["{$schema}/number-ex-min-max", (object)['value' => 1.00001, 'min' => 1, 'max' => 10], true],
            ["{$schema}/number-ex-min-max", (object)['value' => 1, 'min' => 1, 'max' => 10], false],
            ["{$schema}/number-ex-min-max", (object)['value' => 10, 'min' => 1, 'max' => 10], false],
            ["{$schema}/number-ex-min-max", (object)['value' => 0, 'min' => 1, 'max' => 10], false],
            ["{$schema}/number-ex-min-max", (object)['value' => 12, 'min' => 1, 'max' => 10], false],
            ["{$schema}/number-ex-min-max", (object)['value' => 1, 'min' => [1], 'max' => 10], false],
            ["{$schema}/number-ex-min-max", (object)['value' => 1, 'min' => 1, 'max' => [10]], false],

            // number: multipleOf
            ["{$schema}/number-divisor", (object)['value' => 4, 'divisor' => 2], true],
            ["{$schema}/number-divisor", (object)['value' => 4.2, 'divisor' => 2.1], true],
            ["{$schema}/number-divisor", (object)['value' => 4.3, 'divisor' => 2], false],
            ["{$schema}/number-divisor", (object)['value' => -4.5, 'divisor' => 1.5], true],
            ["{$schema}/number-divisor", (object)['value' => 1234.5678, 'divisor' => 0], false],
            ["{$schema}/number-divisor", (object)['value' => -1, 'divisor' => -1], false],
            ["{$schema}/number-divisor", (object)['value' => 0, 'divisor' => 'a'], false],

            // string: minLength & maxLength
            ["{$schema}/string-min-max", (object)['value' => 'abc', 'min' => 1, 'max' => 5], true],
            ["{$schema}/string-min-max", (object)['value' => 'a', 'min' => 1, 'max' => 5], true],
            ["{$schema}/string-min-max", (object)['value' => 'abcde', 'min' => 1, 'max' => 5], true],
            ["{$schema}/string-min-max", (object)['value' => '', 'min' => 1, 'max' => 5], false],
            ["{$schema}/string-min-max", (object)['value' => 'abcdef', 'min' => 1, 'max' => 5], false],
            ["{$schema}/string-min-max", (object)['value' => 'abc', 'min' => -1, 'max' => 5], false],
            ["{$schema}/string-min-max", (object)['value' => 'abc', 'min' => 1, 'max' => -5], false],

            // string: pattern
            ["{$schema}/string-pattern", (object)['value' => 'abc', 'pattern' => '^[a-f]+$'], true],
            ["{$schema}/string-pattern", (object)['value' => 'd', 'pattern' => '^[a-f]+$'], true],
            ["{$schema}/string-pattern", (object)['value' => 'ef', 'pattern' => '^[a-f]+$'], true],
            ["{$schema}/string-pattern", (object)['value' => '', 'pattern' => '^[a-f]+$'], false],
            ["{$schema}/string-pattern", (object)['value' => 'xyz', 'pattern' => '^[a-f]+$'], false],
            ["{$schema}/string-pattern", (object)['value' => 'abc', 'pattern' => '^\d+$'], false],
            ["{$schema}/string-pattern", (object)['value' => '', 'pattern' => '\Z'], false],

            // array: minItems & maxItems
            ["{$schema}/array-min-max", (object)['value' => [1, 2, 3], 'min' => 1, 'max' => 5], true],
            ["{$schema}/array-min-max", (object)['value' => [1], 'min' => 1, 'max' => 5], true],
            ["{$schema}/array-min-max", (object)['value' => [1, 2, 3, 4, 5], 'min' => 1, 'max' => 5], true],
            ["{$schema}/array-min-max", (object)['value' => ['a', 'b', 'c'], 'min' => 1, 'max' => 5], true],
            ["{$schema}/array-min-max", (object)['value' => [], 'min' => 1, 'max' => 5], false],
            ["{$schema}/array-min-max", (object)['value' => [1, 2, 3, 4, 5, 6], 'min' => 1, 'max' => 5], false],
            ["{$schema}/array-min-max", (object)['value' => [1, 2, 3], 'min' => -1, 'max' => 5], false],
            ["{$schema}/array-min-max", (object)['value' => [1, 2, 3], 'min' => 1, 'max' => -5], false],

            // array: uniqueItems
            ["{$schema}/array-unique", (object)['value' => [1, 2, 3], 'unique' => true], true],
            ["{$schema}/array-unique", (object)['value' => [], 'unique' => true], true],
            ["{$schema}/array-unique", (object)['value' => ['a', 1, 'b'], 'unique' => true], true],
            ["{$schema}/array-unique", (object)['value' => [1, 1.0], 'unique' => true], false],
            ["{$schema}/array-unique", (object)['value' => [1, 1.0], 'unique' => false], true],
            ["{$schema}/array-unique", (object)['value' => [1, 2], 'unique' => 5], false],

            // object: minProperties & maxProperties
            ["{$schema}/object-min-max", (object)['value' => (object)['a' => 1, 'b' => 2], 'min' => 1, 'max' => 3], true],
            ["{$schema}/object-min-max", (object)['value' => (object)['a' => 1], 'min' => 1, 'max' => 3], true],
            ["{$schema}/object-min-max", (object)['value' => (object)['a' => 1, 'b' => 2, 'c' => 3], 'min' => 1, 'max' => 3], true],
            ["{$schema}/object-min-max", (object)['value' => (object)['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4], 'min' => 1, 'max' => 3], false],
            ["{$schema}/object-min-max", (object)['value' => (object)[], 'min' => 1, 'max' => 3], false],
            ["{$schema}/object-min-max", (object)['value' => (object)['a' => 1], 'min' => -1, 'max' => 3], false],
            ["{$schema}/object-min-max", (object)['value' => (object)['a' => 1], 'min' => 1, 'max' => -3], false],
        ];
    }
}