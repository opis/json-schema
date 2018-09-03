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

namespace Opis\JsonSchema\Exception;

use Throwable;

class FilterNotFoundException extends AbstractSchemaException
{

    /** @var string */
    protected $type;

    /** @var string */
    protected $filter;

    /**
     * FilterNotFoundException constructor.
     * @param string $type
     * @param string $filter
     * @param Throwable|null $previous
     */
    public function __construct(string $type, string $filter, Throwable $previous = null)
    {
        $this->type = $type;
        $this->filter = $filter;
        parent::__construct("Filter '{$filter}' was not found for '{$type}' type", 0, $previous);
    }

    /**
     * @return string
     */
    public function type(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function filter(): string
    {
        return $this->filter;
    }
}