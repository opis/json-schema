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

class MultipleTypesTest extends AbstractTest
{
    /**
     * @inheritDoc
     */
    public function validationsProvider(): array
    {
        $schema = "file:///types.json#/definitions/combined";

        return [
            [$schema, true, true],
            [$schema, 0.8, true],
            [$schema, "str", true],
            [$schema, [1, 2, 3], true],
            [$schema, (object)['a' => null], true],
            [$schema, null, false],
            [$schema, 1.1, false],
            [$schema, "a", false],
            [$schema, [1, 2], false],
            [$schema, (object)['b' => ''], false],
        ];
    }
}