<?php
/* ===========================================================================
 * Copyright 2018 Zindex Software
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

namespace Opis\JsonSchema;

interface IValidatorHelper
{

    const JSON_TYPES = ["null", "boolean", "number", "integer", "string", "array", "object"];

    /**
     * @param $value
     * @param bool $use_integer
     * @return string
     */
    public function type($value, bool $use_integer = false): string;

    /**
     * @param string $type
     * @return bool
     */
    public function typeExists(string $type): bool;

    /**
     * @param $value
     * @param string|string[] $type
     * @return bool
     */
    public function isValidType($value, $type): bool;

    /**
     * @param string $data
     * @return int
     */
    public function stringLength(string $data): int;

    /**
     * @param int|float $number
     * @param int|float $divisor
     * @return bool
     */
    public function isMultipleOf($number, $divisor): bool;

    /**
     * @param $a
     * @param $b
     * @param array $defaults
     * @return bool
     */
    public function equals($a, $b, array $defaults = null): bool;

}