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

class SlotTest extends AbstractTest
{
    /**
     * @inheritDoc
     */
    public function validationsProvider(): array
    {
        $schema = "file:///slots.json";

        return [
            ["{$schema}#example-user", (object)['email' => 'a@example.com'], true],
            ["{$schema}#example-user", (object)['email' => 'a@example.test'], false],
            ["{$schema}#example-user", (object)['email' => 'a@example.com', 'friend' => []], false],
            ["{$schema}#example-user", (object)['email' => 'a@example.com', 'friend' => (object)['email' => 'b@example.com']], true],
            ["{$schema}#example-user", (object)['email' => 'a@example.com', 'friend' => (object)['email' => 'b@example.test']], false],
            ["{$schema}#example-user", (object)['email' => 'a@example.com', 'friend' => (object)['email' => 'not-email']], false],

            ["{$schema}#user", (object)['email' => 'a@other.example.com'], true, false, null, ['extraEmailValidation' => true]],
            ["{$schema}#user", (object)['email' => 'a@other.example.com'], false, false, null, ['extraEmailValidation' => false]],
            ["{$schema}#user", (object)['email' => 'a@other.example.com', 'friend' => 'any'], true, false, null, ['extraEmailValidation' => true, 'self' => true]],
            ["{$schema}#user", (object)['email' => 'a@other.example.com', 'friend' => 'any'], false, false, null, ['extraEmailValidation' => true, 'self' => (object)['type' => 'number']]],

            ["{$schema}#user", (object)['email' => 'a@example.com', 'friend' => (object)['email' => 'b@example.com']], true, false, null, [
                'extraEmailValidation' => $schema . '#/definitions/example-user/$slots/extraEmailValidation',
                'self' => "{$schema}#user",
            ]],

            ["{$schema}#user", (object)['email' => 'a@example.com', 'friend' => (object)['email' => 'b@example.com']], false, false, null, [
                'extraEmailValidation' => (object)['type' => 'null'],
                'self' => "{$schema}#user",
            ]],

            ["{$schema}#user", (object)['email' => 'a@example.test', 'friend' => (object)['email' => 'b@example.com']], false, false, null, [
                'extraEmailValidation' => $schema . '#/definitions/example-user/%24pass/extraEmailValidation',
                'self' => "{$schema}#user",
            ]],

            ["{$schema}#defaults", 5, true],
            ["{$schema}#defaults", "5", false],
            ["{$schema}#defaults", "5", true, false, null, [
                'number' => (object)['type' => 'string']
            ]],
            ["{$schema}#defaults", 5, false, false, null, [
                'number' => (object)['type' => 'string']
            ]],
        ];
    }
}