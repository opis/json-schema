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

class ValidationContext
{
    /** @var mixed */
    protected $rootData;

    /** @var string[]|int[] */
    protected array $currentDataPath = [];

    protected array $globals;

    /** @var mixed */
    protected $currentData = null;

    protected ?ValidationContext $parent = null;

    protected SchemaLoader $loader;

    protected ?Schema $sender = null;

    /** @var object[]|null[]|null */
    protected ?array $shared = null;

    /** @var null|string[]|Schema[]|object[] */
    protected ?array $slots = null;

    protected int $sharedIndex = -1;

    protected int $pathIndex = 0;

    protected int $maxErrors = 1;

    /**
     * @param $data
     * @param SchemaLoader $loader
     * @param null|ValidationContext $parent
     * @param Schema|null $parent
     * @param array $globals
     * @param null|string[]|Schema[] $slots
     * @param int $max_errors
     */
    public function __construct(
        $data,
        SchemaLoader $loader,
        ?ValidationContext $parent = null,
        ?Schema $sender = null,
        array $globals = [],
        ?array $slots = null,
        int $max_errors = 1
    )
    {
        $this->sender = $sender;
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
     * @param $data
     * @param Schema|null $sender
     * @param array|null $globals
     * @param array|null $slots
     * @param int|null $max_errors
     * @return ValidationContext
     */
    public function newInstance($data, ?Schema $sender, ?array $globals = null, ?array $slots = null, ?int $max_errors = null): ValidationContext
    {
        return new self($data, $this->loader, $this, $sender, $globals ?? $this->globals, $slots ?? $this->slots,
            $max_errors ?? $this->maxErrors);
    }

    public function sender(): ?Schema
    {
        return $this->sender;
    }

    /**
     * @return ValidationContext|null
     */
    public function parent(): ?ValidationContext
    {
        return $this->parent;
    }

    /**
     * @return SchemaLoader
     */
    public function loader(): SchemaLoader
    {
        return $this->loader;
    }

    /**
     * @return mixed
     */
    public function rootData()
    {
        return $this->rootData;
    }

    /**
     * @return mixed
     */
    public function currentData()
    {
        return $this->currentData[$this->pathIndex][0];
    }

    /**
     * @param $value
     */
    public function setCurrentData($value): void
    {
        $this->currentData[$this->pathIndex][0] = $value;
        $this->currentData[$this->pathIndex][1] = false;
    }

    /**
     * @return string|null
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
     * @return int[]|string[]
     */
    public function currentDataPath(): array
    {
        return $this->currentDataPath;
    }

    /**
     * @param string|int $key
     * @return $this
     */
    public function pushDataPath($key): ValidationContext
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
     * @return $this
     */
    public function popDataPath(): ValidationContext
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
     * @return array
     */
    public function globals(): array
    {
        return $this->globals;
    }

    /**
     * @param array $globals
     * @param bool $overwrite
     * @return $this
     */
    public function setGlobals(array $globals, bool $overwrite = false): ValidationContext
    {
        if ($overwrite) {
            $this->globals = $globals;
        } elseif ($globals) {
            $this->globals = $globals + $this->globals;
        }

        return $this;
    }

    /**
     * @param object|null $object
     * @return $this
     */
    public function pushSharedObject(?object $object = null): ValidationContext
    {
        $this->shared[] = $object;
        $this->sharedIndex++;

        return $this;
    }

    /**
     * @return $this
     */
    public function popSharedObject(): ValidationContext
    {
        if ($this->sharedIndex >= 0) {
            array_pop($this->shared);
            $this->sharedIndex--;
        }

        return $this;
    }

    /**
     * @return object|null
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
     * @return object[]|Schema[]|string[]|null
     */
    public function slots(): ?array
    {
        return $this->slots;
    }

    /**
     * @param array|null $slots
     * @return $this
     */
    public function setSlots(?array $slots): ValidationContext
    {
        if ($slots) {
            $list = [];

            foreach ($slots as $name => $value) {
                if (is_bool($value)) {
                    $value = $this->loader->loadBooleanSchema($value);
                } elseif (is_object($value)) {
                    if ($value instanceof Schema) {
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

                if ($value instanceof Schema) {
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
     * @param string $name
     * @return Schema|null
     */
    public function slot(string $name): ?Schema
    {
        return $this->slots[$name] ?? null;
    }

    /**
     * @return int
     */
    public function maxErrors(): int
    {
        return $this->maxErrors;
    }

    /**
     * @param int $max
     * @return $this
     */
    public function setMaxErrors(int $max): ValidationContext
    {
        $this->maxErrors = $max;

        return $this;
    }
}