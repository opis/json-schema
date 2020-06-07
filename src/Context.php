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

class Context implements IContext
{
    /** @var mixed */
    protected $rootData;

    /** @var string[]|int[] */
    protected array $currentDataPath = [];

    protected array $globals;

    /** @var mixed */
    protected $currentData = null;

    protected ?IContext $parent = null;

    protected ISchemaLoader $loader;

    /** @var object[]|null[]|null */
    protected ?array $shared = null;

    /** @var null|string[]|ISchema[]|object[] */
    protected ?array $slots = null;

    protected int $sharedIndex = -1;

    protected int $pathIndex = 0;

    protected int $maxErrors = 1;

    /**
     * @param $data
     * @param ISchemaLoader $loader
     * @param null|IContext $parent
     * @param array $globals
     * @param null|string[]|ISchema[] $slots
     * @param int $max_errors
     */
    public function __construct(
        $data,
        ISchemaLoader $loader,
        ?IContext $parent = null,
        array $globals = [],
        ?array $slots = null,
        int $max_errors = 1
    )
    {
        $this->rootData = $data;
        $this->loader = $loader;
        $this->parent = $parent;
        $this->globals = $globals;
        $this->slots = null;
        $this->maxErrors = $max_errors;
        $this->currentData = [
            [$data, false],
        ];

        if ($slots) {
            $this->setSlots($slots);
        }
    }

    /**
     * @inheritDoc
     */
    public function newInstance($data, ?array $globals = null, ?array $slots = null, ?int $max_errors = null): IContext
    {
        return new self($data, $this->loader, $this, $globals ?? $this->globals, $slots ?? $this->slots,
            $max_errors ?? $this->maxErrors);
    }

    /**
     * @inheritDoc
     */
    public function parent(): ?IContext
    {
        return $this->parent;
    }

    /**
     * @inheritDoc
     */
    public function loader(): ISchemaLoader
    {
        return $this->loader;
    }

    /**
     * @inheritDoc
     */
    public function rootData()
    {
        return $this->rootData;
    }

    /**
     * @inheritDoc
     */
    public function currentData()
    {
        return $this->currentData[$this->pathIndex][0];
    }

    /**
     * @inheritDoc
     */
    public function setCurrentData($value): void
    {
        $this->currentData[$this->pathIndex][0] = $value;
        $this->currentData[$this->pathIndex][1] = false;
    }

    /**
     * @inheritDoc
     */
    public function currentDataType(): ?string
    {
        $type = $this->currentData[$this->pathIndex][1];
        if ($type === false) {
            $type = Helper::getJsonType($this->currentData[$this->pathIndex][0]);
            $this->currentData[$this->pathIndex][1] = $type;
        }

        return $type;
    }

    /**
     * @inheritDoc
     */
    public function currentDataPath(): array
    {
        return $this->currentDataPath;
    }

    /**
     * @inheritDoc
     */
    public function pushDataPath($key): IContext
    {
        $this->currentDataPath[] = $key;

        $data = $this->currentData[$this->pathIndex][0];

        if (is_array($data)) {
            $data = $data[$key] ?? null;
        } elseif (is_object($data)) {
            $data = $data->{$key} ?? null;
        } else {
            $data = null;
        }

        $this->currentData[] = [$data, false];
        $this->pathIndex++;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function popDataPath(): IContext
    {
        if ($this->pathIndex < 1) {
            return $this;
        }

        array_pop($this->currentDataPath);
        array_pop($this->currentData);
        $this->pathIndex--;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function globals(): array
    {
        return $this->globals;
    }

    /**
     * @inheritDoc
     */
    public function setGlobals(array $globals, bool $overwrite = false): IContext
    {
        if ($overwrite) {
            $this->globals = $globals;
        } elseif ($globals) {
            $this->globals = $globals + $this->globals;
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function pushSharedObject(?object $object = null): IContext
    {
        $this->shared[] = $object;
        $this->sharedIndex++;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function popSharedObject(): IContext
    {
        if ($this->sharedIndex >= 0) {
            array_pop($this->shared);
            $this->sharedIndex--;
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function sharedObject(): ?object
    {
        if ($this->sharedIndex < 0) {
            return null;
        }

        $obj = $this->shared[$this->sharedIndex];
        if ($obj === null) {
            $obj = $this->shared[$this->sharedIndex] = (object) [];
        }

        return $obj;
    }

    /**
     * @inheritDoc
     */
    public function slots(): ?array
    {
        return $this->slots;
    }

    /**
     * @inheritDoc
     */
    public function setSlots(?array $slots): IContext
    {
        if ($slots) {
            $list = [];

            foreach ($slots as $name => $value) {
                if (is_bool($value)) {
                    $value = $this->loader->loadBooleanSchema($value);
                } elseif (is_object($value)) {
                    if ($value instanceof ISchema) {
                        $list[$name] = $value;
                        continue;
                    }
                    $value = $this->loader->loadObjectSchema($value);
                } elseif (is_string($value)) {
                    if (isset($this->slots[$value])) {
                        $value = $this->slots[$value];
                    } elseif ($this->parent) {
                        $value = $this->parent->slot($value);
                    }
                }

                if ($value instanceof ISchema) {
                    $list[$name] = $value;
                }
            }

            $this->slots = $list;
        } else {
            $this->slots = null;
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function slot(string $name): ?ISchema
    {
        return $this->slots[$name] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function maxErrors(): int
    {
        return $this->maxErrors;
    }

    /**
     * @inheritDoc
     */
    public function setMaxErrors(int $max): IContext
    {
        $this->maxErrors = $max;

        return $this;
    }
}