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

class ValidatorHelper implements IValidatorHelper
{

    /** @var int */
    protected $scale;

    /** @var string|callable */
    protected $strLengthFunc;

    /** @var bool */
    protected $useBCMath = false;

    /**
     * ValidatorHelperX constructor.
     * @param int $scale
     */
    public function __construct(int $scale = 10)
    {
        $this->scale = $scale;
        if (class_exists('\\Opis\String\\UnicodeString', true)) {
            $this->strLengthFunc = function (string $data): int {
                return \Opis\String\UnicodeString::from($data)->length();
            };
        }
        else {
            $this->strLengthFunc = function_exists('mb_strlen') ? 'mb_strlen' : 'strlen';
        }
        $this->useBCMath = function_exists('bcdiv');
    }

    /**
     * @inheritDoc
     */
    public function type($value, bool $use_integer = false): string
    {
        if (is_null($value)) {
            return "null";
        }
        if (is_bool($value)) {
            return "boolean";
        }
        if (is_int($value)) {
            return $use_integer ? "integer" : "number";
        }
        if (is_float($value)) {
            return $use_integer && $this->isMultipleOf($value, 1) ? "integer" : "number";
        }
        if (is_string($value)) {
            return "string";
        }
        if (is_array($value)) {
            // Check if is an indexed array
            $ok = true;
            for ($i = 0, $max = count($value); $i < $max; $i++) {
                if (!array_key_exists($i, $value)) {
                    $ok = false;
                    break;
                }
            }
            if ($ok) {
                return "array";
            }
        }
        // Otherwise consider it an object
        return "object";
    }

    /**
     * @inheritDoc
     */
    public function typeExists(string $type): bool
    {
        return in_array($type, static::JSON_TYPES, true);
    }

    /**
     * @inheritDoc
     */
    public function isValidType($value, $type): bool
    {
        $value_type = $this->type($value, true);
        if (is_string($type)) {
            return $value_type === $type || ($value_type === 'integer' && $type === 'number');
        } elseif (!is_array($type)) {
            return false;
        }
        return in_array($value_type, $type, true) ||
            ($value_type === 'integer' && in_array('number', $type, true));
    }

    /**
     * @inheritDoc
     */
    public function stringLength(string $data): int
    {
        return ($this->strLengthFunc)($data);
    }

    /**
     * @inheritDoc
     */
    public function isMultipleOf($number, $divisor): bool
    {
        if ($number == 0) {
            return true;
        }
        if (!$this->useBCMath) {
            return 0 == $number - $divisor * (int)($number / $divisor);
        }

        $number = number_format($number, $this->scale, '.', '');
        $divisor = number_format($divisor, $this->scale, '.', '');

        $x = bcdiv($number, $divisor, 0);
        $x = bcmul($divisor, $x, $this->scale);
        $x = bcsub($number, $x, $this->scale);
        return 0 === bccomp($x, 0, $this->scale);
    }

    /**
     * @inheritDoc
     */
    public function equals($a, $b, array $defaults = null): bool
    {
        $a_type = $this->type($a, false);
        $b_type = $this->type($b, false);
        if ($a_type !== $b_type) {
            return false;
        }
        if ($a_type === "array") {
            $count = count($a);
            if ($count !== count($b)) {
                return false;
            }
            for ($i = 0; $i < $count; $i++) {
                if (!array_key_exists($i, $a) || !array_key_exists($i, $b)) {
                    return false;
                }
                if (!$this->equals($a[$i], $b[$i])) {
                    return false;
                }
            }
            return true;
        }
        if ($a_type === "object") {
            if ($a === $b) {
                return true;
            }
            $a = get_object_vars($a);
            if ($a === null) {
                return false;
            }
            if ($defaults) {
                $a += $defaults;
            }
            $b = get_object_vars($b);
            if ($b === null) {
                return false;
            }
            if (count($a) !== count($b)) {
                return false;
            }
            foreach ($a as $prop => $value) {
                if (!array_key_exists($prop, $b)) {
                    return false;
                }
                if (!$this->equals($value, $b[$prop])) {
                    return false;
                }
            }
            return true;
        }
        if ($a_type === 'number') {
            return $a == $b;
        }
        return $a === $b;
    }
}