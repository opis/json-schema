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

namespace Opis\JsonSchema\Formats;

class DateTimeFormats
{
    const DATE_REGEX = '/^(\d{4})-(0[1-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01])$/i';
    const TIME_REGEX = '/^([01][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9]|60)(\.[0-9]+)?(Z|(\+|-)([01][0-9]|2[0-3]):([0-5][0-9]))?$/i';
    const DATE_TIME_REGEX = '/^(\d{4})-(0[1-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01])T([01][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9]|60)(\.[0-9]+)?(Z|(\+|-)([01][0-9]|2[0-3]):([0-5][0-9]))?$/i';
    const DURATION_REGEX = '/^P?((((\d+D)|((\d+M(\d+D)?)|(\d+Y(\d+M(\d+D)?)?)))(T((\d+S)|(\d+M(\d+S)?)|(\d+H(\d+M(\d+S)?)?)))?)|(T((\d+S)|(\d+M(\d+S)?)|(\d+H(\d+M(\d+S)?)?)))|(\d+W))$/i';

    /**
     * @param string $value
     * @return bool
     */
    public static function date(string $value): bool
    {
        if (preg_match(self::DATE_REGEX, $value, $m)) {
            return checkdate($m[2], $m[3], $m[1]);
        }

        return false;
    }

    /**
     * @param string $value
     * @return bool
     */
    public static function time(string $value): bool
    {
        return (bool)preg_match(self::TIME_REGEX, $value);
    }

    /**
     * @param string $value
     * @return bool
     */
    public static function dateTime(string $value): bool
    {
        if (preg_match(self::DATE_TIME_REGEX, $value, $m)) {
            return checkdate($m[2], $m[3], $m[1]);
        }

        return false;
    }

    /**
     * @param string $value
     * @return bool
     */
    public static function duration(string $value): bool
    {
        return (bool) preg_match(self::DURATION_REGEX, $value);
    }
}