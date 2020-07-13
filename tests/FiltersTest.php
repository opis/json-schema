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

use Opis\JsonSchema\Parsers\SchemaParser;
use Opis\JsonSchema\Resolvers\DefaultFilterResolver;

class FiltersTest extends AbstractTest
{
    /**
     * @inheritDoc
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        /** @var SchemaParser $parser */
        $parser = self::$validator->parser();
        /** @var DefaultFilterResolver $filters */
        $filters = $parser->getFilterResolver();

        $filters->registerCallable('string', 'oddLen', function (string $value): bool {
            return strlen($value) % 2 === 1;
        });

        $filters->registerCallable('number', 'modulo', function (float $number, array $args): bool {
            return $number % $args['divisor'] == $args['reminder'];
        });
    }

    /**
     * @inheritDoc
     */
    public function validationsProvider(): array
    {
        $globals = [
            'divisor' => 2,
            'reminder' => 0,
        ];

        $schema = "file:///filter.json#/definitions";

        return [
            ["{$schema}/simple", "a", true],
            ["{$schema}/simple", "AbC", true],
            ["{$schema}/simple", "ab", false],
            ["{$schema}/simple", "", false],
            ["{$schema}/simple", 52, false],
            // args
            ["{$schema}/vars", 5, true],
            ["{$schema}/vars", 2, true],
            ["{$schema}/vars", 2.5, false],
            ["{$schema}/vars", 6, false],
            ["{$schema}/vars", 7, false],
            // syntax sugar
            ["{$schema}/syntax-sugar", 5, true],
            ["{$schema}/syntax-sugar", 7, false],
            // multi
            ["{$schema}/multi", 5, true],
            ["{$schema}/multi", 11, true],
            ["{$schema}/multi", 6, false],
            ["{$schema}/multi", 7, false],
            // str
            ["{$schema}/str", "a", true],
            ["{$schema}/str", "ab", false],
            // globals
            ["{$schema}/globals", 6, true, false, $globals],
            ["{$schema}/globals", 12, true, false, $globals],
            ["{$schema}/globals", 2, false, false, $globals],
            ["{$schema}/globals", 8, false, false, $globals],
            // invalid
            ["{$schema}/invalid", 10, false, true],
        ];
    }
}