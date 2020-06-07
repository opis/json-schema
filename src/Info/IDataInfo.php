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

interface IDataInfo
{
    /**
     * Data value
     * @return mixed
     */
    public function value();

    /**
     * Json data type
     * @return string|null
     */
    public function type(): ?string;

    /**
     * Root data that holds the current value somewhere
     * @return mixed
     */
    public function root();

    /**
     * Path to data value, starting from root
     * @return string[]|int[]
     */
    public function path(): array;

    /**
     * Absolute path to data
     * @return string[]|int[]
     */
    public function fullPath(): array;

    /**
     * @return IDataInfo|null
     */
    public function parent(): ?IDataInfo;
}