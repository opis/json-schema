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

interface IContext
{
    /**
     * @return null|IContext
     */
    public function parent(): ?self;

    /**
     * @return ISchemaLoader
     */
    public function loader(): ISchemaLoader;

    /**
     * Current data
     * @return mixed
     */
    public function rootData();

    /**
     * @return mixed
     */
    public function currentData();

    /**
     * @param mixed $value
     */
    public function setCurrentData($value): void;

    /**
     * Current data type
     * @return string|null
     */
    public function currentDataType(): ?string;

    /**
     * Path to this data
     * @return string[]|int[]
     */
    public function currentDataPath(): array;

    /**
     * @param int|string $key
     * @return IContext
     */
    public function pushDataPath($key): self;

    /**
     * @return IContext
     */
    public function popDataPath(): self;

    /**
     * @param object|null $object
     * @return IContext
     */
    public function pushSharedObject(?object $object = null): self;

    /**
     * @return IContext
     */
    public function popSharedObject(): self;

    /**
     * @return null|object
     */
    public function sharedObject(): ?object;

    /**
     * @return array
     */
    public function globals(): array;

    /**
     * @param array $globals
     * @param bool $overwrite
     * @return IContext
     */
    public function setGlobals(array $globals, bool $overwrite = false): self;

    /**
     * @return ISchema[]|null
     */
    public function slots(): ?array;

    /**
     * @param string[]|object[]|ISchema[]|null $slots
     * @return IContext
     */
    public function setSlots(?array $slots): self;

    /**
     * @param string $name
     * @return null|ISchema
     */
    public function slot(string $name): ?ISchema;

    /**
     * @return int
     */
    public function maxErrors(): int;

    /**
     * @param int $max
     * @return IContext
     */
    public function setMaxErrors(int $max): self;

    /**
     * @param $data
     * @param array|null $globals
     * @param null|string[]|ISchema[] $slots
     * @param int|null $max_errors
     * @return IContext
     */
    public function newInstance($data, ?array $globals = null, ?array $slots = null, ?int $max_errors = 1): self;
}