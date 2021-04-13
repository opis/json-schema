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

use JsonSerializable;
use Opis\Uri\Uri as BaseUri;

class Uri extends BaseUri implements JsonSerializable
{
    /**
     * @var bool Set this to true and the qs will always be sorted
     */
    protected static bool $useNormalizedComponents = false;

    public function __construct(array $components)
    {
        if (static::$useNormalizedComponents) {
            $components = self::normalizeComponents($components);
        }
        parent::__construct($components);
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): string
    {
        return $this->__toString();
    }

    /**
     * @param string $uri
     * @param bool $ensure_fragment
     * @return static|null
     */
    public static function parse(string $uri, bool $ensure_fragment = false): ?self
    {
        if ($ensure_fragment && strpos($uri, '#') === false) {
            $uri .= '#';
        }

        return self::create($uri);
    }

    /**
     * @param string|array|static $uri
     * @param string|array|static $base
     * @param bool $ensure_fragment
     * @return static|null
     */
    public static function merge($uri, $base, bool $ensure_fragment = false): ?self
    {
        $uri = self::resolveComponents($uri);

        if ($uri === null) {
            return null;
        }

        if ($ensure_fragment && !isset($uri['fragment'])) {
            $uri['fragment'] = '';
        }

        $base = self::resolveComponents($base);

        if (!$base) {
            return new self($uri);
        }

        return new self(self::mergeComponents($uri, $base));
    }

    /**
     * @param bool $value
     */
    public static function useNormalizedComponents(bool $value): void
    {
        self::$useNormalizedComponents = $value;
    }
}
