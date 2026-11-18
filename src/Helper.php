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

namespace Opis\JsonSchema;

final class Helper
{
    /** @var string[] */
    public const JSON_TYPES = ['string', 'number', 'boolean', 'null', 'object', 'array'];

    /** @var string[] */
    public const JSON_SUBTYPES = ['integer' => 'number'];

    /** @var string[] */
    protected const PHP_TYPE_MAP = [
        'NULL' => 'null',
        'integer' => 'integer',
        'double' => 'number',
        'boolean' => 'boolean',
        'array' => 'array',
        'object' => 'object',
        'string' => 'string',
    ];

    /**
     * @param string $type
     * @return bool
     */
    public static function isValidJsonType(string $type): bool
    {
        if (isset(self::JSON_SUBTYPES[$type])) {
            return true;
        }

        return in_array($type, self::JSON_TYPES, true);
    }

    /**
     * @param string $type
     * @return null|string
     */
    public static function getJsonSuperType(string $type): ?string
    {
        return self::JSON_SUBTYPES[$type] ?? null;
    }

    /**
     * @param mixed $value
     * @param bool $use_subtypes
     * @return null|string
     */
    public static function getJsonType($value, bool $use_subtypes = true): ?string
    {
        $type = self::PHP_TYPE_MAP[gettype($value)] ?? null;
        if ($type === null) {
            return null;
        } elseif ($type === 'array') {
            return self::isIndexedArray($value) ? 'array' : null;
        }

        if ($use_subtypes) {
            if ($type === 'number' && self::isMultipleOf($value, 1)) {
                return 'integer';
            }
        } elseif ($type === 'integer') {
            return 'number';
        }

        return $type;
    }

    /**
     * @param string $type
     * @param string|string[] $allowed
     * @return bool
     */
    public static function jsonTypeMatches(string $type, $allowed): bool
    {
        if (!$allowed) {
            return false;
        }

        if (is_string($allowed)) {
            if ($type === $allowed) {
                return true;
            }

            return $allowed === self::getJsonSuperType($type);
        }

        if (is_array($allowed)) {
            if (in_array($type, $allowed, true)) {
                return true;
            }

            if ($type = self::getJsonSuperType($type)) {
                return in_array($type, $allowed, true);
            }
        }

        return false;
    }

    /**
     * @param mixed $value
     * @param string|string[] $type
     * @return bool
     */
    public static function valueIsOfJsonType($value, $type): bool
    {
        $t = self::getJsonType($value);
        if ($t === null) {
            return false;
        }

        return self::jsonTypeMatches($t, $type);
    }

    /**
     * @param array $array
     * @return bool
     */
    public static function isIndexedArray(array $array): bool
    {
        for ($i = 0, $max = count($array); $i < $max; $i++) {
            if (!array_key_exists($i, $array)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Converts assoc-arrays to objects (recursive)
     * @param scalar|object|array|null $schema
     * @return scalar|object|array|null
     */
    public static function convertAssocArrayToObject($schema)
    {
        if (is_null($schema) || is_scalar($schema)) {
            return $schema;
        }

        $keepArray = is_array($schema) && self::isIndexedArray($schema);

        $data = [];

        foreach ($schema as $key => $value) {
            $data[$key] = is_array($value) || is_object($value) ? self::convertAssocArrayToObject($value) : $value;
        }

        return $keepArray ? $data : (object) $data;
    }

    /**
     * @param mixed $a
     * @param mixed $b
     * @return bool
     */
    public static function equals($a, $b): bool
    {
        if ($a === $b) {
            return true;
        }

        $type = self::getJsonType($a, false);
        if ($type === null || $type !== self::getJsonType($b, false)) {
            return false;
        }

        if ($type === 'number') {
            return $a == $b;
        }

        if ($type === "array") {
            $count = count($a);
            if ($count !== count($b)) {
                return false;
            }

            for ($i = 0; $i < $count; $i++) {
                if (!array_key_exists($i, $a) || !array_key_exists($i, $b)) {
                    return false;
                }
                if (!self::equals($a[$i], $b[$i])) {
                    return false;
                }
            }

            return true;
        }

        if ($type === "object") {
            $a = get_object_vars($a);
            if ($a === null) {
                return false;
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
                if (!self::equals($value, $b[$prop])) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }


    /**
     * @var bool|null True if bcmath extension is available
     */
    private static ?bool $hasBCMath = null;

    /**
     * @var bool True to use bcmath
     */
    public static bool $useBCMath = true;

    /**
     * @var int Number scale to used when using comparisons
     */
    public static int $numberScale = 14;

    /**
     * @param $number
     * @param $divisor
     * @param int|null $scale
     * @return bool
     */
    public static function isMultipleOf($number, $divisor, ?int $scale = null): bool
    {
        if (!$number || $number == $divisor) {
            return true;
        }

        if ($divisor == 0) {
            return $number == 0;
        }

        // maybe we get lucky
        if (!fmod($number, $divisor)) {
            return true;
        }

        $divisor = abs($divisor);
        if ($divisor == 1) {
            // should have been caught by the above fmod()
            return false;
        }

        $number = abs($number);
        if ($divisor > $number) {
            // number / divisor < 1
            return false;
        }

        // Use global scale if null
        $scale ??= self::$numberScale;

        if (
            !self::$useBCMath ||
            !(self::$hasBCMath ??= extension_loaded('bcmath'))
        ) {
            // use an approximation
            return abs($number - round($number / $divisor) * $divisor) < (10 ** -$scale);
        }

        // use bcmath
        //
        // number_format($n, $scale) prints fraction digits past the significant
        // precision of a float (32.49 -> 32.49000000000001, 2222.22 -> 2222.21999999999980).
        // Divide the shortest round-trip decimal instead.
        // https://github.com/opis/json-schema/issues/165

        $number = self::decimalString($number);
        $divisor = self::decimalString($divisor);

        if (!is_string($number) || !is_string($divisor)) {
            return false;
        }

        $scale = max($scale, self::fractionDigits($number), self::fractionDigits($divisor));

        if (bccomp($divisor, '0', $scale) === 0) {
            return bccomp($number, '0', $scale) === 0;
        }

        $x = bcdiv($number, $divisor, 0);
        $x = bcmul($divisor, $x, $scale);
        $x = bcsub($number, $x, $scale);

        return bccomp($x, '0', $scale) === 0;
    }

    /**
     * Shortest round-trip decimal, with scientific notation expanded.
     * 1.0e-5 becomes 0.00001, 1.0e+20 becomes 100000000000000000000.
     * BCMath does not accept exponent syntax.
     *
     * @param int|float $number
     * @return string|null
     */
    private static function decimalString($number): ?string
    {
        if (is_int($number)) {
            return (string) $number;
        }

        $previous = ini_set('serialize_precision', '-1');
        $encoded = json_encode($number, JSON_PRESERVE_ZERO_FRACTION);
        if ($previous !== false) {
            ini_set('serialize_precision', $previous);
        }

        if (!is_string($encoded) || preg_match('/\A(-?)(\d+)(?:\.(\d+))?(?:[eE]([+-]?\d+))?\z/', $encoded, $matches) !== 1) {
            return null;
        }

        $digits = $matches[2] . ($matches[3] ?? '');
        $point = strlen($matches[2]) + (int) ($matches[4] ?? 0);

        if ($point <= 0) {
            $plain = '0.' . str_repeat('0', -$point) . $digits;
        } elseif ($point >= strlen($digits)) {
            $plain = $digits . str_repeat('0', $point - strlen($digits));
        } else {
            $plain = substr($digits, 0, $point) . '.' . substr($digits, $point);
        }

        if (strpos($plain, '.') !== false) {
            $plain = rtrim(rtrim($plain, '0'), '.');
        }

        if ($plain === '' || $plain === '0') {
            return '0';
        }

        return $matches[1] === '-' ? '-' . $plain : $plain;
    }

    /**
     * @param string $number
     * @return int
     */
    private static function fractionDigits(string $number): int
    {
        $point = strpos($number, '.');

        return $point === false ? 0 : strlen($number) - $point - 1;
    }

    /**
     * @param $value
     * @return mixed
     */
    public static function cloneValue($value)
    {
        if ($value === null || is_scalar($value)) {
            return $value;
        }

        if (is_array($value)) {
            return array_map(self::class . '::cloneValue', $value);
        }

        if (is_object($value)) {
            return (object)array_map(self::class . '::cloneValue', get_object_vars($value));
        }

        return null;
    }

    /**
     * @param string $pattern
     * @return bool
     */
    public static function isValidPattern(string $pattern): bool
    {
        if (strpos($pattern, '\Z') !== false) {
            return false;
        }

        return @preg_match("\x07{$pattern}\x07u", '') !== false;
    }

    /**
     * @param string $pattern
     * @return string
     */
    public static function patternToRegex(string $pattern): string
    {
        return "\x07{$pattern}\x07uD";
    }

    /**
     * @param mixed $data
     * @return mixed
     */
    public static function toJSON($data)
    {
        if ($data === null || is_scalar($data)) {
            return $data;
        }

        $map = [];

        $isArray = true;
        $index = 0;
        foreach ($data as $key => $value) {
            $map[$key] = self::toJSON($value);
            if ($isArray) {
                if ($index !== $key) {
                    $isArray = false;
                } else {
                    $index++;
                }
            }
        }

        if ($isArray) {
            if (!$map && is_object($data)) {
                return (object) $map;
            }
            return $map;
        }

        return (object) $map;
    }
}
