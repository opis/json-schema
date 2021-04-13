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

use finfo;
use Opis\JsonSchema\ContentMediaType;

class ContentMediaTypeResolver
{
    /** @var callable[]|ContentMediaType[] */
    protected array $media;

    /** @var callable|null|ContentMediaType */
    protected $defaultMedia = null;

    /**
     * @param callable[]|ContentMediaType[] $media
     * @param callable|ContentMediaType|null $defaultMedia
     */
    public function __construct(array $media = [], $defaultMedia = null)
    {
        $media += [
            'application/json' => self::class . '::IsJsonEncoded',
        ];

        $this->media = $media;
        $this->defaultMedia = $defaultMedia ?? self::class . '::IsEncodedAsType';
    }

    /**
     * @param string $name
     * @return callable|ContentMediaType|string|null
     */
    public function resolve(string $name)
    {
        return $this->media[$name] ?? $this->defaultMedia;
    }

    /**
     * @param string $name
     * @param ContentMediaType $media
     * @return ContentMediaTypeResolver
     */
    public function register(string $name, ContentMediaType $media): self
    {
        $this->media[$name] = $media;

        return $this;
    }

    /**
     * @param string $name
     * @param callable $media
     * @return ContentMediaTypeResolver
     */
    public function registerCallable(string $name, callable $media): self
    {
        $this->media[$name] = $media;

        return $this;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function unregister(string $name): bool
    {
        if (isset($this->media[$name])) {
            unset($this->media[$name]);

            return true;
        }

        return false;
    }

    /**
     * @param callable|ContentMediaType|null $handler
     * @return ContentMediaTypeResolver
     */
    public function setDefaultHandler($handler): self
    {
        $this->defaultMedia = $handler;

        return $this;
    }

    public function __serialize(): array
    {
        return [
            'media' => $this->media,
            'defaultMedia' => $this->defaultMedia,
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->media = $data['media'];
        $this->defaultMedia = $data['defaultMedia'];
    }

    public static function IsJsonEncoded(string $value,
        /** @noinspection PhpUnusedParameterInspection */ string $type): bool
    {
        json_decode($value);

        return json_last_error() === JSON_ERROR_NONE;
    }

    public static function IsEncodedAsType(string $value, string $type): bool
    {
        /** @var finfo|null|bool $finfo */
        static $finfo = false;

        if ($finfo === false) {
            if (!class_exists(finfo::class)) {
                $finfo = null;
                return false;
            }
            $finfo = new finfo(FILEINFO_MIME_TYPE);
        } elseif (!$finfo) {
            return false;
        }

        $r = $finfo->buffer($value);

        return $r == $type || $r == 'application/x-empty';
    }
}