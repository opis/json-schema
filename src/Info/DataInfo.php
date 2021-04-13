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

use Opis\JsonSchema\ValidationContext;

class DataInfo
{
    /** @var mixed */
    protected $value;

    protected ?string $type;

    /** @var mixed */
    protected $root;

    /** @var string[]|int[] */
    protected array $path;

    protected ?DataInfo $parent = null;

    /** @var string[]|int[]|null */
    protected ?array $fullPath = null;

    /**
     * DataInfo constructor.
     * @param $value
     * @param string|null $type
     * @param $root
     * @param string[]|int[] $path
     * @param DataInfo|null $parent
     */
    public function __construct($value, ?string $type, $root, array $path = [], ?DataInfo $parent = null)
    {
        $this->value = $value;
        $this->type = $type;
        $this->root = $root;
        $this->path = $path;
        $this->parent = $parent;
    }

    public function value()
    {
        return $this->value;
    }

    public function type(): ?string
    {
        return $this->type;
    }

    public function root()
    {
        return $this->root;
    }

    /**
     * @return int[]|string[]
     */
    public function path(): array
    {
        return $this->path;
    }

    public function parent(): ?DataInfo
    {
        return $this->parent;
    }

    /**
     * @return int[]|string[]
     */
    public function fullPath(): array
    {
        if ($this->parent === null) {
            return $this->path;
        }

        if ($this->fullPath === null) {
            $this->fullPath = array_merge($this->parent->fullPath(), $this->path);
        }

        return $this->fullPath;
    }

    /**
     * @param ValidationContext $context
     * @return static
     */
    public static function fromContext(ValidationContext $context): self
    {
        if ($parent = $context->parent()) {
            $parent = self::fromContext($parent);
        }

        return new self($context->currentData(), $context->currentDataType(), $context->rootData(),
            $context->currentDataPath(), $parent);
    }
}