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

use Opis\JsonSchema\Uri;
use Opis\Uri\UriTemplate;

class UriFormats
{
    /**
     * @param string $value
     * @return bool
     */
    public static function uri(string $value): bool
    {
        if ($value === '') {
            return false;
        }

        $uri = Uri::parse($value);

        return $uri !== null && $uri->isAbsolute();
    }

    /**
     * @param string $value
     * @return bool
     */
    public static function uriReference(string $value): bool
    {
        if ($value === '') {
            return true;
        }

        return Uri::parse($value) !== null;
    }

    /**
     * @param string $value
     * @return bool
     */
    public static function uriTemplate(string $value): bool
    {
        if ($value === '') {
            return true;
        }

        if (UriTemplate::isTemplate($value)) {
            return true;
        }

        return Uri::parse($value) !== null;
    }
}