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

class IntegerTypeTest extends AbstractTest
{
    /**
     * @inheritDoc
     */
    public function validationsProvider(): array
    {
        $schema = "file:///types.json#/definitions/integer";

        return [
            [$schema, 5, true],
            [$schema, -10, true],
            [$schema, 8.0, true],
            [$schema, -6.0, true],
            [$schema, 5.5, false],
            [$schema, -8.5, false],
            [$schema, "1", false],
            [$schema, "0", false],
            [$schema, "-2", false],
            [$schema, null, false],
            [$schema, false, false],
            [$schema, [1], false],
            [$schema, (object)['a' => 1], false],
        ];
    }
}