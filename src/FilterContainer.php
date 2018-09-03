<?php
/* ===========================================================================
 * Copyright 2018 Zindex Software
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

class FilterContainer implements IFilterContainer
{

    /** @var array */
    protected $container = [];

    /**
     * @inheritDoc
     */
    public function get(string $type, string $name)
    {
        return $this->container[$type][$name] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function hasType(string $type): bool
    {
        return isset($this->container[$type]);
    }

    /**
     * @param string $type
     * @param string $name
     * @param IFilter $filter
     * @return FilterContainer
     */
    public function add(string $type, string $name, IFilter $filter): self
    {
        $this->container[$type][$name] = $filter;
        return $this;
    }

}