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

namespace Opis\JsonSchema\Resolvers;

use Opis\JsonSchema\IContentEncoding;

class ContentEncodingResolver implements IContentEncodingResolver
{
    /** @var callable[]|IContentEncoding[] */
    protected array $encodings;

    /**
     * @param callable[]|IContentEncoding[] $encodings
     */
    public function __construct(array $encodings = [])
    {
        $encodings += [
            'binary' => static function (string $value): ?string {
                return $value;
            },
            'base64' => static function (string $value): ?string {
                $value = base64_decode($value, true);

                return is_string($value) ? $value : null;
            },
            'quoted-printable' => static function (string $value): ?string {
                return quoted_printable_encode($value);
            },
        ];

        $this->encodings = $encodings;
    }

    /**
     * @inheritDoc
     */
    public function resolve(string $name)
    {
        return $this->encodings[$name] ?? null;
    }

    /**
     * @param string $name
     * @param IContentEncoding $encoding
     * @return ContentEncodingResolver
     */
    public function register(string $name, IContentEncoding $encoding): self
    {
        $this->encodings[$name] = $encoding;

        return $this;
    }

    /**
     * @param string $name
     * @param callable $encoding
     * @return ContentEncodingResolver
     */
    public function registerCallable(string $name, callable $encoding): self
    {
        $this->encodings[$name] = $encoding;

        return $this;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function unregister(string $name): bool
    {
        if (isset($this->encodings[$name])) {
            unset($this->encodings[$name]);

            return true;
        }

        return false;
    }
}