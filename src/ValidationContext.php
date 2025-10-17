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

use ArrayObject;
use Opis\String\UnicodeString;
use Opis\JsonSchema\Errors\ValidationError;

class ValidationContext
{
    /** @var mixed */
    protected $rootData;

    /** @var string[]|int[] */
    protected array $currentDataPath = [];

    protected ?array $fullPath = null;

    protected array $globals;

    /** @var mixed */
    protected $currentData = null;

    protected ?self $parent = null;

    protected SchemaLoader $loader;

    protected ?Schema $sender = null;

    /** @var object[]|null[]|null */
    protected ?array $shared = null;

    /** @var null|string[]|Schema[]|object[] */
    protected ?array $slots = null;

    protected int $sharedIndex = -1;

    protected int $pathIndex = 0;

    protected int $maxErrors = 1;

    protected bool $stopAtFirstError = true;

    /**
     * @param $data
     * @param SchemaLoader $loader
     * @param null|self $parent
     * @param Schema|null $parent
     * @param array $globals
     * @param null|string[]|Schema[] $slots
     * @param int $max_errors
     */
    public function __construct(
        $data,
        SchemaLoader $loader,
        ?self $parent = null,
        ?Schema $sender = null,
        array $globals = [],
        ?array $slots = null,
        int $max_errors = 1,
        bool $stop_at_first_error = true
    ) {
        $this->sender = $sender;
        $this->rootData = $data;
        $this->loader = $loader;
        $this->parent = $parent;
        $this->globals = $globals;
        $this->slots = null;
        $this->maxErrors = $max_errors;
        $this->stopAtFirstError = $stop_at_first_error;
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
     * @return self
     */
    public function newInstance(
        $data,
        ?Schema $sender,
        ?array $globals = null,
        ?array $slots = null,
        ?int $max_errors = null,
        ?bool $stop_at_first_error = null
    ): self {
        return new self(
            $data,
            $this->loader,
            $this,
            $sender,
                $globals ?? $this->globals,
                $slots ?? $this->slots,
            $max_errors ?? $this->maxErrors,
            $stop_at_first_error ?? $this->stopAtFirstError
        );
    }

    public function create(
        Schema $sender,
        ?Variables $mapper = null,
        ?Variables $globals = null,
        ?array $slots = null,
        ?int $maxErrors = null,
        ?bool $stop_at_first_error = null
    ): self {
        if ($globals) {
            $globals = $globals->resolve($this->rootData(), $this->currentDataPath());
            if (!is_array($globals)) {
                $globals = (array)$globals;
            }
            $globals += $this->globals;
        } else {
            $globals = $this->globals;
        }

        if ($mapper) {
            $data = $mapper->resolve($this->rootData(), $this->currentDataPath());
        } else {
            $data = $this->currentData();
        }

        return new self($data, $this->loader, $this, $sender, $globals, $slots ?? $this->slots,
            $maxErrors ?? $this->maxErrors, $stop_at_first_error ?? $this->stopAtFirstError);
    }

    public function sender(): ?Schema
    {
        return $this->sender;
    }

    /**
     * @return self|null
     */
    public function parent(): ?self
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

    public function fullDataPath(): array
    {
        if ($this->fullPath === null) {
            if ($this->parent === null) {
                return $this->currentDataPath;
            }
            $this->fullPath = array_merge($this->parent->fullDataPath(), $this->currentDataPath);
        }

        return $this->fullPath;
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
    public function pushDataPath($key): self
    {
        $this->currentDataPath[] = $key;
        if ($this->fullPath !== null) {
            $this->fullPath[] = $key;
        }

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
    public function popDataPath(): self
    {
        if ($this->pathIndex < 1) {
            return $this;
        }

        if ($this->fullPath !== null) {
            array_pop($this->fullPath);
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
    public function setGlobals(array $globals, bool $overwrite = false): self
    {
        if ($overwrite) {
            $this->globals = $globals;
        } elseif ($globals) {
            $this->globals = $globals + $this->globals;
        }

        return $this;
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
    public function setSlots(?array $slots): self
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
    public function setMaxErrors(int $max): self
    {
        $this->maxErrors = $max;

        return $this;
    }


    public function stopAtFirstError(): bool
    {
        return $this->stopAtFirstError;
    }

    public function setStopAtFirstError(bool $stop): self
    {
        $this->stopAtFirstError = $stop;

        return $this;
    }

    /* --------------------- */

    /**
     * @param Schema $schema
     * @return $this
     */
    public function pushSharedObject(Schema $schema): self
    {
        $unevaluated = !in_array($schema->info()->draft(), ['06', '07']);
        if ($unevaluated && ($parser = $this->loader->parser()) && !$parser->option('allowUnevaluated', true)) {
            $unevaluated = false;
        }

        $this->shared[] = [
            'schema' => $schema,
            'unevaluated' => $unevaluated,
            'object' => null,
        ];
        $this->sharedIndex++;

        return $this;
    }

    /**
     * @return $this
     */
    public function popSharedObject(): self
    {
        if ($this->sharedIndex < 0) {
            return $this;
        }

        $data = array_pop($this->shared);
        $this->sharedIndex--;

        if ($data['unevaluated'] && $data['object']) {
            if ($this->sharedIndex >= 0) {
                $this->mergeUnevaluated($data['object']);
            } elseif ($this->parent && $this->parent->sharedIndex >= 0) {
                $this->parent->mergeUnevaluated($data['object']);
            }
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

        return $this->shared[$this->sharedIndex]['object'] ??= (object)[];
    }

    public function schema(): ?Schema
    {
        return $this->shared[$this->sharedIndex]['schema'] ?? null;
    }

    public function trackUnevaluated(): bool
    {
        return $this->shared[$this->sharedIndex]['unevaluated'] ?? false;
    }

    protected function mergeUnevaluated(object $obj): void
    {
        switch ($this->currentDataType()) {
            case 'object':
                if (isset($obj->evaluatedProperties)) {
                    $this->addEvaluatedProperties($obj->evaluatedProperties);
                }
                break;
            case 'array':
                if (isset($obj->evaluatedItems)) {
                    $this->addEvaluatedItems($obj->evaluatedItems);
                }
                break;
        }
    }

    /* ----------------*/

    public function getStringLength(): ?int
    {
        if ($this->currentDataType() !== 'string') {
            return null;
        }

        $shared = $this->sharedObject();

        if (!isset($shared->stringLength)) {
            $shared->stringLength = UnicodeString::getStringLength($this->currentData());
        }

        return $shared->stringLength;
    }

    public function setDecodedContent(string $content): bool
    {
        if ($this->currentDataType() !== 'string') {
            return false;
        }

        $this->sharedObject()->decodedContent = $content;

        return true;
    }

    public function getDecodedContent(): ?string
    {
        if ($this->currentDataType() !== 'string') {
            return null;
        }
        return $this->sharedObject()->decodedContent ?? $this->currentData();
    }

    public function getObjectProperties(): ?array
    {
        if ($this->currentDataType() !== 'object') {
            return null;
        }

        return $this->sharedObject()->objectProperties ??= array_keys(get_object_vars($this->currentData()));
    }

    public function addCheckedProperties(?array $properties): bool
    {
        if (!$properties) {
            return false;
        }

        $shared = $this->sharedObject();

        if (!isset($shared->checkedProperties)) {
            $shared->checkedProperties = $properties;
        } else {
            $shared->checkedProperties = array_values(array_unique(array_merge($shared->checkedProperties, $properties)));
        }

        return true;
    }

    public function getCheckedProperties(): ?array
    {
        return $this->sharedObject()->checkedProperties ?? null;
    }

    public function getUncheckedProperties(): ?array
    {
        $properties = $this->getObjectProperties();
        if (!$properties) {
            return $properties;
        }

        $checked = $this->sharedObject()->checkedProperties ?? null;
        if (!$checked) {
            return $properties;
        }

        return array_values(array_diff($properties, $checked));
    }

    public function markAllAsEvaluatedProperties(): bool
    {
        return $this->addEvaluatedProperties($this->getObjectProperties());
    }

    public function addEvaluatedProperties(?array $properties): bool
    {
        if (!$properties || !($this->currentDataType() === 'object') || !$this->trackUnevaluated()) {
            return false;
        }

        $shared = $this->sharedObject();

        if (!isset($shared->evaluatedProperties)) {
            $shared->evaluatedProperties = $properties;
        } else {
            $shared->evaluatedProperties = array_values(array_unique(array_merge($shared->evaluatedProperties, $properties)));
        }

        return true;
    }

    public function getEvaluatedProperties(): ?array
    {
        return $this->sharedObject()->evaluatedProperties ?? null;
    }

    public function getUnevaluatedProperties(): ?array
    {
        $properties = $this->getObjectProperties();
        if (!$properties) {
            return $properties;
        }

        $evaluated = $this->sharedObject()->evaluatedProperties ?? null;
        if (!$evaluated) {
            return $properties;
        }

        return array_values(array_diff($properties, $evaluated));
    }

    public function markAllAsEvaluatedItems(): bool
    {
        return $this->addEvaluatedItems(range(0, count($this->currentData())));
    }

    public function markCountAsEvaluatedItems(int $count): bool
    {
        if (!$count) {
            return false;
        }

        return $this->addEvaluatedItems(range(0, $count));
    }

    public function addEvaluatedItems(?array $items): bool
    {
        if (!$items || !($this->currentDataType() === 'array') || !$this->trackUnevaluated()) {
            return false;
        }

        $shared = $this->sharedObject();

        if (!isset($shared->evaluatedItems)) {
            $shared->evaluatedItems = $items;
        } else {
            $shared->evaluatedItems = array_values(array_unique(array_merge($shared->evaluatedItems, $items), SORT_NUMERIC));
        }

        return true;
    }

    public function getEvaluatedItems(): ?array
    {
        return $this->sharedObject()->evaluatedItems ?? null;
    }

    public function getUnevaluatedItems(): ?array
    {
        if ($this->currentDataType() !== 'array') {
            return null;
        }

        $items = array_keys($this->currentData());
        if (!$items) {
            return $items;
        }

        $evaluated = $this->sharedObject()->evaluatedItems ?? null;
        if (!$evaluated) {
            return $items;
        }

        return array_values(array_diff($items, $evaluated));
    }

    public function validateSchemaWithoutEvaluated(
        Schema $schema,
        ?int $maxErrors = null,
        bool $reset_on_error_only = false,
        ?ArrayObject $array = null
    ): ?ValidationError {
        $currentMaxErrors = $this->maxErrors;

        $this->maxErrors = $maxErrors ?? $currentMaxErrors;

        if ($this->trackUnevaluated()) {
            $shared = $this->sharedObject();

            $props = $shared->evaluatedProperties ?? null;
            $items = $shared->evaluatedItems ?? null;

            $error = $schema->validate($this);

            if ($array) {
                $value = null;

                if ($shared->evaluatedProperties ?? null) {
                    if ($props) {
                        if ($diff = array_diff($shared->evaluatedProperties, $props)) {
                            $value['properties'] = $diff;
                        }
                    } else {
                        $value['properties'] = $shared->evaluatedProperties;
                    }
                }

                if ($shared->evaluatedItems ?? null) {
                    if ($items) {
                        if ($diff = array_diff($shared->evaluatedItems, $items)) {
                            $value['items'] = $diff;
                        }
                    } else {
                        $value['items'] = $shared->evaluatedItems;
                    }
                }

                if ($value) {
                    $array[] = $value;
                }
            }

            if ($reset_on_error_only) {
                if ($error) {
                    $shared->evaluatedProperties = $props;
                    $shared->evaluatedItems = $items;
                }
            } else {
                $shared->evaluatedProperties = $props;
                $shared->evaluatedItems = $items;
            }
        } else {
            $error = $schema->validate($this);
        }

        $this->maxErrors = $currentMaxErrors;

        return $error;
    }
}