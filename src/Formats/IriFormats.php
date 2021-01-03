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

use Throwable;
use Opis\JsonSchema\Uri;

class IriFormats
{
    private const SKIP = [0x23, 0x26, 0x2F, 0x3A, 0x3D, 0x3F, 0x40, 0x5B, 0x5C, 0x5D];

    /** @var bool|null|callable */
    private static $idn = false;

    /**
     * @param string $value
     * @return bool
     */
    public static function iri(string $value): bool
    {
        if ($value === '') {
            return false;
        }

        try {
            $components = Uri::parseComponents(Uri::encodeComponent($value, self::SKIP), true, true);
        } catch (Throwable $e) {
            return false;
        }

        return isset($components['scheme']) && $components['scheme'] !== '';
    }

    /**
     * @param string $value
     * @return bool
     */
    public static function iriReference(string $value): bool
    {
        if ($value === '') {
            return true;
        }

        try {
            return Uri::parseComponents(Uri::encodeComponent($value, self::SKIP), true, true) !== null;
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * @param string $value
     * @param callable|null $idn
     * @return bool
     */
    public static function idnHostname(string $value, ?callable $idn = null): bool
    {
        $idn = $idn ?? static::idn();

        if ($idn) {
            $value = $idn($value);
            if ($value === null) {
                return false;
            }
        }

        return Uri::isValidHost($value);
    }

    /**
     * @param string $value
     * @param callable|null $idn
     * @return bool
     */
    public static function idnEmail(string $value, ?callable $idn = null): bool
    {
        $idn = $idn ?? static::idn();

        if ($idn) {
            if (!preg_match('/^(?<name>.+)@(?<domain>.+)$/u', $value, $m)) {
                return false;
            }

            $m['name'] = $idn($m['name']);
            if ($m['name'] === null) {
                return false;
            }

            $m['domain'] = $idn($m['domain']);
            if ($m['domain'] === null) {
                return false;
            }

            $value = $m['name'] . '@' . $m['domain'];
        }

        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * @return callable|null
     */
    public static function idn(): ?callable
    {
        if (static::$idn === false) {
            if (function_exists('idn_to_ascii')) {
                static::$idn = static function (string $value): ?string {
                    /** @noinspection PhpComposerExtensionStubsInspection */
                    $value = idn_to_ascii($value, 0, INTL_IDNA_VARIANT_UTS46);

                    return is_string($value) ? $value : null;
                };
            } else {
                static::$idn = null;
            }
        }

        return static::$idn;
    }
}