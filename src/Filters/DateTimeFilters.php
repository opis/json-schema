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

namespace Opis\JsonSchema\Filters;

use DateTime, DateTimeZone;

final class DateTimeFilters
{
    public static function MinDate(string $date, array $args): bool
    {
        $min = $args['value'];
        $tz = $args['timezone'] ?? null;

        return self::CreateDate($date, $tz, false) >= self::CreateDate($min, $tz, false);
    }

    public static function MaxDate(string $date, array $args): bool
    {
        $max = $args['value'];
        $tz = $args['timezone'] ?? null;

        return self::CreateDate($date, $tz, false) <= self::CreateDate($max, $tz, false);
    }

    public static function NotDate(string $date, array $args): bool
    {
        $not = $args['value'];
        $tz = $args['timezone'] ?? null;

        if (!is_array($not)) {
            $not = [$not];
        }

        $date = self::CreateDate($date, $tz, false);

        foreach ($not as $d) {
            if ($date == self::CreateDate($d, $tz, false)) {
                return false;
            }
        }

        return true;
    }

    public static function MinDateTime(string $date, array $args): bool
    {
        $min = $args['value'];
        $tz = $args['timezone'] ?? null;

        return self::CreateDate($date, $tz) >= self::CreateDate($min, $tz);
    }

    public static function MaxDateTime(string $date, array $args): bool
    {
        $max = $args['value'];
        $tz = $args['timezone'] ?? null;

        return self::CreateDate($date, $tz) <= self::CreateDate($max, $tz);
    }

    public static function MinTime(string $time, array $args): bool
    {
        $min = $args['value'];
        $prefix = '1970-01-01 ';

        return self::CreateDate($prefix . $time) >= self::CreateDate($prefix . $min);
    }

    public static function MaxTime(string $time, array $args): bool
    {
        $max = $args['value'];
        $prefix = '1970-01-01 ';

        return self::CreateDate($prefix . $time) <= self::CreateDate($prefix . $max);
    }

    private static function CreateDate(string $value, ?string $timezone = null, bool $time = true): DateTime
    {
        $date = new DateTime($value, $timezone ? new DateTimeZone($timezone) : null);
        if (!$time) {
            return $date->setTime(0, 0, 0, 0);
        }
        return $date;
    }
}