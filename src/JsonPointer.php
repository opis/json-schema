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

final class JsonPointer
{
    /** @var string */
    protected const PATTERN = '~^(?:(?<level>0|[1-9][0-9]*)(?<shift>(?:\+|-)(?:0|[1-9][0-9]*))?)?(?<pointer>(?:/[^/#]*)*)(?<fragment>#)?$~';

    /** @var string */
    protected const UNESCAPED = '/~([^01]|$)/';

    protected int $level = -1;

    protected int $shift = 0;

    protected bool $fragment = false;

    /** @var string[]|int[] */
    protected array $path;

    protected ?string $str = null;

    final protected function __construct(array $path, int $level = -1, int $shift = 0, bool $fragment = false)
    {
        $this->path = $path;
        $this->level = $level < 0 ? -1 : $level;
        $this->shift = $shift;
        $this->fragment = $level >= 0 && $fragment;
    }

    public function isRelative(): bool
    {
        return $this->level >= 0;
    }

    public function isAbsolute(): bool
    {
        return $this->level < 0;
    }

    public function level(): int
    {
        return $this->level;
    }

    public function shift(): int
    {
        return $this->shift;
    }

    /**
     * @return string[]
     */
    public function path(): array
    {
        return $this->path;
    }

    /**
     * @return bool
     */
    public function hasFragment(): bool
    {
        return $this->fragment;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        if ($this->str === null) {
            if ($this->level >= 0) {
                $this->str = (string)$this->level;

                if ($this->shift !== 0) {
                    if ($this->shift > 0) {
                        $this->str .= '+';
                    }
                    $this->str .= $this->shift;
                }

                if ($this->path) {
                    $this->str .= '/';
                    $this->str .= implode('/', self::encodePath($this->path));
                }

                if ($this->fragment) {
                    $this->str .= '#';
                }
            } else {
                $this->str = '/';
                $this->str .= implode('/', self::encodePath($this->path));
            }
        }

        return $this->str;
    }

    /**
     * @param $data
     * @param array|null $path
     * @param null $default
     * @return mixed
     */
    public function data($data, ?array $path = null, $default = null)
    {
        if ($this->level < 0) {
            return self::getData($data, $this->path, false, $default);
        }

        if ($path !== null) {
            $path = $this->absolutePath($path);
        }

        if ($path === null) {
            return $default;
        }

        return self::getData($data, $path, $this->fragment, $default);
    }

    /**
     * @param array $path
     * @return array|null
     */
    public function absolutePath(array $path = []): ?array
    {
        if ($this->level < 0) {
            // Absolute pointer
            return $this->path;
        }

        if ($this->level === 0) {
            if ($this->shift && !$this->handleShift($path)) {
                return null;
            }
            return $this->path ? array_merge($path, $this->path) : $path;
        }

        $count = count($path);
        if ($count === $this->level) {
            if ($this->shift) {
                return null;
            }
            return $this->path;
        }

        if ($count > $this->level) {
            $count -= $this->level;

            /** @var array $path */
            $path = array_slice($path, 0, $count);

            if ($this->shift && !$this->handleShift($path, $count)) {
                return null;
            }

            return $this->path ? array_merge($path, $this->path) : $path;
        }

        return null;
    }

    protected function handleShift(array &$path, ?int $count = null): bool
    {
        if (!$path) {
            return false;
        }

        $count ??= count($path);

        $last = $path[$count - 1];

        if (is_string($last) && preg_match('/^[1-9]\d*$/', $last)) {
            $last = (int) $last;
        }

        if (!is_int($last)) {
            return false;
        }

        $path[$count - 1] = $last + $this->shift;

        return true;
    }

    public static function parse(string $pointer, bool $decode = true): ?self
    {
        if ($pointer === '' || !preg_match(self::PATTERN, $pointer, $m)) {
            // Not a pointer
            return null;
        }

        $pointer = $m['pointer'] ?? null;

        // Check if the pointer is escaped
        if ($decode && $pointer && preg_match(self::UNESCAPED, $pointer)) {
            // Invalid pointer
            return null;
        }

        $level = isset($m['level']) && $m['level'] !== ''
            ? (int)$m['level']
            : -1;

        $shift = 0;
        if ($level >= 0 && isset($m['shift']) && $m['shift'] !== '') {
            $shift = (int) $m['shift'];
        }

        $fragment = isset($m['fragment']) && $m['fragment'] === '#';
        unset($m);

        if ($fragment && $level < 0) {
            return null;
        }

        if ($pointer === '') {
            $pointer = null;
        } elseif ($pointer !== null) {
            // Remove leading slash
            $pointer = substr($pointer, 1);

            if ($pointer !== '') {
                $pointer = self::decodePath(explode('/', $pointer));
            } else {
                $pointer = null;
            }
        }

        return new self($pointer ?? [], $level, $shift, $fragment);
    }

    /**
     * @param $data
     * @param array|null $path
     * @param bool $fragment
     * @param null $default
     * @return mixed
     */
    public static function getData($data, ?array $path = null, bool $fragment = false, $default = null)
    {
        if ($path === null) {
            return $default;
        }

        if (!$path) {
            return $fragment ? $default : $data;
        }

        if ($fragment) {
            return end($path);
        }

        foreach ($path as $key) {
            if (is_array($data)) {
                if (!array_key_exists($key, $data)) {
                    return $default;
                }
                $data = $data[$key];
            } elseif (is_object($data)) {
                if (!property_exists($data, $key)) {
                    return $default;
                }
                $data = $data->{$key};
            } else {
                return $default;
            }
        }

        return $data;
    }

    /**
     * @param string|string[] $path
     * @return string|string[]
     */
    public static function encodePath($path)
    {
        $path = str_replace('~', '~0', $path);
        $path = str_replace('/', '~1', $path);

        if (is_array($path)) {
            return array_map('rawurlencode', $path);
        }

        return rawurlencode($path);
    }

    /**
     * @param string|string[] $path
     * @return string|string[]
     */
    public static function decodePath($path)
    {
        if (is_array($path)) {
            $path = array_map('rawurldecode', $path);
        } else {
            $path = rawurldecode($path);
        }

        $path = str_replace('~1', '/', $path);
        $path = str_replace('~0', '~', $path);

        return $path;
    }

    /**
     * @param array $path
     * @return string
     */
    public static function pathToString(array $path): string
    {
        if (!$path) {
            return '/';
        }

        return '/' . implode('/', self::encodePath($path));
    }

    /**
     * @param array $path
     * @return string
     */
    public static function pathToFragment(array $path): string
    {
        if (!$path) {
            return '#';
        }

        return '#/' . implode('/', self::encodePath($path));
    }

    /**
     * @param string $pointer
     * @return bool
     */
    public static function isAbsolutePointer(string $pointer): bool
    {
        if ($pointer === '/') {
            return true;
        }

        if (!preg_match(self::PATTERN, $pointer, $m)) {
            return false;
        }

        if (isset($m['fragment']) || isset($m['level']) && $m['level'] !== '') {
            return false;
        }

        if (!isset($m['pointer']) || $m['pointer'] === '') {
            return true;
        }

        return !preg_match(self::UNESCAPED, $m['pointer']);
    }

    /**
     * @param string $pointer
     * @return bool
     */
    public static function isRelativePointer(string $pointer): bool
    {
        if ($pointer === '') {
            return false;
        }

        if (!preg_match(self::PATTERN, $pointer, $m)) {
            return false;
        }

        if (!isset($m['level']) || $m['level'] === '' || (int)$m['level'] < 0) {
            return false;
        }

        if (!isset($m['pointer']) || $m['pointer'] === '') {
            return true;
        }

        return !preg_match(self::UNESCAPED, $m['pointer']);
    }

    public static function createAbsolute(array $path): self
    {
        return new self($path, -1, 0, false);
    }

    public static function createRelative(int $level, array $path = [], int $shift = 0, bool $fragment = false): self
    {
        return new self($path, $level, $shift, $fragment);
    }
}