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

class NumberTypeTest extends AbstractTest
{
    /**
     * @inheritDoc
     */
    public function validationsProvider(): array
    {
        $schema = "file:///types.json#/definitions/number";

        return [
            ["{$schema}/simple", 5, true],
            ["{$schema}/simple", -10, true],
            ["{$schema}/simple", 5.8, true],
            ["{$schema}/simple", -8.5, true],
            ["{$schema}/simple", "0", false],
            ["{$schema}/simple", "-2.5", false],

            // min, max
            ["{$schema}/interval", -0.8, true],
            ["{$schema}/interval", 5, true],
            ["{$schema}/interval", 7.5, true],
            ["{$schema}/interval", 7.51, false],
            ["{$schema}/interval", -0.81, false],

            // exclusive min, max
            ["{$schema}/interval_exclusive", 5, true],
            ["{$schema}/interval_exclusive", -0.8, false],
            ["{$schema}/interval_exclusive", -0.81, false],
            ["{$schema}/interval_exclusive", 7.5, false],
            ["{$schema}/interval_exclusive", 7.51, false],

            // multiple
            ["{$schema}/multiple", 1, true],
            ["{$schema}/multiple", 0.4, true],
            ["{$schema}/multiple", -4.6, true],
            ["{$schema}/multiple", 1.1, false],
            ["{$schema}/multiple", -0.25, false],
        ];
    }
}