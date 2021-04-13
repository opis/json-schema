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

class MiscFormats
{
    const UUID_REGEX = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';

    /**
     * @param string $value
     * @return bool
     */
    public static function ipv4(string $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false;
    }

    /**
     * @param string $value
     * @return bool
     */
    public static function ipv6(string $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false;
    }

    /**
     * @param string $value
     * @return bool
     */
    public static function email(string $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * @param string $value
     * @return bool
     */
    public static function uuid(string $value): bool
    {
        return (bool) preg_match(self::UUID_REGEX, $value);
    }
}