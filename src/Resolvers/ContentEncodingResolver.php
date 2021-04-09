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

use Opis\JsonSchema\ContentEncoding;

class ContentEncodingResolver
{
    /** @var callable[]|ContentEncoding[] */
    protected array $list;

    /** @var callable|ContentEncoding|null */
    protected $defaultEncoding = null;

    /**
     * @param callable[]|ContentEncoding[] $list
     * @param callable|ContentEncoding|null $defaultEncoding
     */
    public function __construct(array $list = [], $defaultEncoding = null)
    {
        $list += [
            'binary' => self::class . '::DecodeBinary',
            'base64' => self::class . '::DecodeBase64',
            'quoted-printable' => self::class . '::DecodeQuotedPrintable',
        ];

        $this->list = $list;
        $this->defaultEncoding = $defaultEncoding;
    }

    /**
     * @param string $name
     * @return callable|ContentEncoding|string|null
     */
    public function resolve(string $name)
    {
        return $this->list[$name] ?? $this->defaultEncoding;
    }

    /**
     * @param string $name
     * @param ContentEncoding $encoding
     * @return ContentEncodingResolver
     */
    public function register(string $name, ContentEncoding $encoding): self
    {
        $this->list[$name] = $encoding;

        return $this;
    }

    /**
     * @param string $name
     * @param callable $encoding
     * @return ContentEncodingResolver
     */
    public function registerCallable(string $name, callable $encoding): self
    {
        $this->list[$name] = $encoding;

        return $this;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function unregister(string $name): bool
    {
        if (isset($this->list[$name])) {
            unset($this->list[$name]);

            return true;
        }

        return false;
    }

    /**
     * @param callable|ContentEncoding|null $handler
     * @return $this
     */
    public function setDefaultHandler($handler): self
    {
        $this->defaultEncoding = $handler;
        return $this;
    }

    public function __serialize(): array
    {
        return [
            'list' => $this->list,
            'defaultEncoding' => $this->defaultEncoding,
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->list = $data['list'];
        $this->defaultEncoding = $data['defaultEncoding'] ?? null;
    }

    public static function DecodeBinary(string $value): ?string
    {
        return $value;
    }

    public static function DecodeBase64(string $value): ?string
    {
        $value = base64_decode($value, true);

        return is_string($value) ? $value : null;
    }

    public static function DecodeQuotedPrintable(string $value): ?string
    {
        return quoted_printable_decode($value);
    }
}