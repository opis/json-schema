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

class JsonPointer
{

    const POINTER_REGEX = '~^(?:/|(?:/[^/#]*)*)$~';

    const RELATIVE_POINTER_REGEX = '~^(?<level>0|[1-9][0-9]*)(?<pointer>(?:/[^/#]+)*)(?<fragment>#?)$~';

    const POINTER_NOT_ESCAPED_REGEX = '/~([^01]|$)/';


    /**
     * @param string $pointer
     * @return bool
     */
    public static function isPointer(string $pointer): bool
    {
        if ($pointer === '') {
            return true;
        }
        return (bool) preg_match(static::POINTER_REGEX, $pointer);
    }

    /**
     * @param string $pointer
     * @return bool
     */
    public static function isRelativePointer(string $pointer): bool
    {
        return (bool) preg_match(static::RELATIVE_POINTER_REGEX, $pointer);
    }

    /**
     * @param string $pointer
     * @return bool
     */
    public static function isEscapedPointer(string $pointer): bool
    {
        return !preg_match(static::POINTER_NOT_ESCAPED_REGEX, $pointer);
    }

    /**
     * @param string $pointer
     * @param bool $parts
     * @return array|null
     */
    public static function parseRelativePointer(string $pointer, bool $parts = false)
    {
        if (!preg_match(static::RELATIVE_POINTER_REGEX, $pointer, $m)) {
            return null;
        }

        return [
            'level' => (int)$m['level'],
            'pointer' => $parts ? static::parsePointer($m['pointer'] ?? '') : $m['pointer'] ?? '',
            'fragment' => isset($m['fragment']) && $m['fragment'] === '#',
        ];
    }

    /**
     * @param string $pointer
     * @return array
     */
    public static function parsePointer(string $pointer): array
    {
        $pointer = trim($pointer, '/');
        if ($pointer === '') {
            return [];
        }
        $must_replace = strpos($pointer, '~') !== false;
        $pointer = explode('/', $pointer);
        if ($must_replace) {
            $pointer = str_replace('~1', '/', $pointer);
            $pointer = str_replace('~0', '~', $pointer);
        }
        $pointer = array_map('rawurldecode', $pointer);
        return $pointer;
    }

    /**
     * @param array $path
     * @return string
     */
    public static function buildPointer(array $path): string
    {
        if (empty($path)) {
            return '/';
        }
        $path = str_replace('~', '~0', $path);
        $path = str_replace('/', '~1', $path);
        $path = implode('/', $path);
        if ($path !== '' && $path[0] !== '/') {
            $path = '/' . $path;
        }
        return $path;
    }

    /**
     * @param int $level
     * @param string|array $path
     * @param bool $fragment
     * @return string
     */
    public static function buildRelativePointer(int $level, $path, bool $fragment = false): string
    {
        if ($level < 0) {
            $level = 0;
        }
        if (is_string($path)) {
            if ($path === '') {
                $path = '/';
            } elseif ($path[0] !== '/') {
                $path = '/' . $path;
            }
        } elseif (is_array($path)) {
            $path = static::buildPointer($path);
        } else {
            $path = '/';
        }

        if ($fragment) {
            $path .= '#';
        }

        return $level . $path;
    }

    /**
     * @param $container
     * @param array|string $pointer
     * @param null $default
     * @param bool $fragment
     * @return mixed|null
     */
    public static function getDataByPointer($container, $pointer, $default = null, bool $fragment = false)
    {
        if (!is_array($pointer)) {
            if (!is_string($pointer)) {
                return $default;
            }
            $pointer = static::parsePointer($pointer);
        }

        $path = $default;
        foreach ($pointer as $path) {
            if (is_array($container)) {
                if (array_key_exists($path, $container)) {
                    $container = $container[$path];
                    continue;
                }
            } elseif (is_object($container)) {
                if (property_exists($container, $path)) {
                    $container = $container->{$path};
                    continue;
                }
            }
            return $default;
        }

        return $fragment ? $path : $container;
    }

    /**
     * @param $container
     * @param array|string $relative
     * @param array|string $base
     * @param mixed|null $default
     * @return mixed|null
     */
    public static function getDataByRelativePointer($container, $relative, $base, $default = null)
    {
        if (!is_array($relative)) {
            if (!is_string($relative)) {
                return $default;
            }
            $relative = static::parseRelativePointer($relative, true);
            if (!$relative) {
                return $default;
            }
        }

        if (!isset($relative['level']) || !isset($relative['pointer'])) {
            return $default;
        }

        if (!is_array($relative['pointer'])) {
            $relative['pointer'] = static::parsePointer($relative['pointer']);
        }

        if (!is_array($base)) {
            $base = static::parsePointer($base);
        }

        if ($relative['level'] > count($base)) {
            return $default;
        }

        if ($relative['level'] > 0) {
            array_splice($base, -$relative['level']);
        }
        if (!empty($relative['pointer'])) {
            $base = array_merge($base, $relative['pointer']);
        }
        $fragment = $relative['fragment'] ?? false;
        unset($relative);
        return static::getDataByPointer($container, $base, $default, $fragment);
    }
}