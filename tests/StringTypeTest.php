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

class StringTypeTest extends AbstractTest
{
    /**
     * @inheritDoc
     */
    public function validationsProvider(): array
    {
        $schema = "file:///types.json#/definitions/string";

        return [
            ["{$schema}/simple", "some string", true],
            ["{$schema}/simple", 5.67, false],

            //  format
            ["{$schema}/format", "name@example.com", true],
            ["{$schema}/format", "name(at)example.com", false],

            // length
            ["{$schema}/length", "AA", true],
            ["{$schema}/length", "AAAAA", true],
            ["{$schema}/length", "", false],
            ["{$schema}/length", "A", false],
            ["{$schema}/length", str_repeat('A', 11), false],

            ["{$schema}/length", "ĂȘ", true],
            ["{$schema}/length", "Î", false],

            // pattern
            ["{$schema}/pattern", "abc", true],
            ["{$schema}/pattern", "abc/", true],
            ["{$schema}/pattern", "Abc", false],
            ["{$schema}/pattern", "1abc", false],
            ["{$schema}/pattern", "", false],
            ["{$schema}/pattern", "a b c", false],

            // encoding
            ["{$schema}/encoding", base64_encode("test"), true, false, null, null, ['2019-09']],
            ["{$schema}/encoding", "tes(t)", false, false, null, null, ['2019-09']],

            // media
            ["{$schema}/media", '{"a": 1}', true, false, null, null, ['2019-09']],
            ["{$schema}/media", '{"a": x}', false, false, null, null, ['2019-09']],

            // enc + media
            ["{$schema}/encoding-media", base64_encode('{"a": 1}'), true, false, null, null, ['2019-09']],
            ["{$schema}/encoding-media", '{"a": 1}', false, false, null, null, ['2019-09']],

            // content
            ["{$schema}/content", '[1, 2, 3, 4.0]', true, false, null, null, ['2019-09']],
            ["{$schema}/content", '[1, null]', false, false, null, null, ['2019-09']],
            ["{$schema}/content", 'abc', false, false, null, null, ['2019-09']],

            // enc + content
            ["{$schema}/encoding-content", base64_encode('[1, 2, 3, 4.0]'), true, false, null, null, ['2019-09']],
            ["{$schema}/encoding-content", base64_encode('[1, null]'), false, false, null, null, ['2019-09']],
            ["{$schema}/encoding-content", base64_encode('abc'), false, false, null, null, ['2019-09']],
        ];
    }
}