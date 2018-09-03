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

class InvalidJsonPointerException extends AbstractSchemaException
{

    /** @var string */
    protected $pointer;

    /**
     * InvalidJsonPointer constructor.
     * @param string $pointer
     * @param Throwable|null $previous
     */
    public function __construct(string $pointer, Throwable $previous = null)
    {
        $this->pointer = $pointer;
        parent::__construct("Invalid JSON pointer: $pointer", 0, $previous);
    }

    /**
     * @return string
     */
    protected function pointer(): string
    {
        return $this->pointer;
    }
}