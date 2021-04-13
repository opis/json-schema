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

namespace Opis\JsonSchema\Info;

use Opis\JsonSchema\Uri;

class SchemaInfo
{
    /** @var bool|object */
    protected $data;

    protected ?Uri $id;

    protected ?Uri $root;

    protected ?Uri $base;

    /** @var string[]|int[] */
    protected array $path;

    protected ?string $draft;

    /**
     * @param object|bool $data
     * @param Uri|null $id
     * @param Uri|null $base
     * @param Uri|null $root
     * @param string[]|int[] $path
     * @param string|null $draft
     */
    public function __construct($data, ?Uri $id, ?Uri $base = null, ?Uri $root = null, array $path = [], ?string $draft = null)
    {
        if ($root === $id || ((string)$root === (string)$id)) {
            $root = null;
        }

        if ($root === null) {
            $base = null;
        }

        $this->data = $data;
        $this->id = $id;
        $this->root = $root;
        $this->base = $base;
        $this->path = $path;
        $this->draft = $draft;
    }

    public function id(): ?Uri
    {
        return $this->id;
    }

    public function root(): ?Uri
    {
       return $this->root;
    }

    public function base(): ?Uri
    {
        return $this->base;
    }

    public function draft(): ?string
    {
        return $this->draft;
    }

    public function data()
    {
        return $this->data;
    }

    public function path(): array
    {
        return $this->path;
    }

    /**
     * Returns first non-null property: id, base or root
     * @return Uri|null
     */
    public function idBaseRoot(): ?Uri
    {
        return $this->id ?? $this->base ?? $this->root;
    }

    public function isBoolean(): bool
    {
        return is_bool($this->data);
    }

    public function isObject(): bool
    {
        return is_object($this->data);
    }

    public function isDocumentRoot(): bool
    {
        return $this->id && !$this->root && !$this->base;
    }
}