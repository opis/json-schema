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

namespace Opis\JsonSchema\Errors;

use Countable, Iterator;

class ErrorContainer implements Countable, Iterator
{

    protected int $maxErrors;

    /** @var ValidationError[] */
    protected array $errors = [];

    /**
     * ErrorContainer constructor.
     * @param int $max_errors
     */
    public function __construct(int $max_errors = 1)
    {
        if ($max_errors < 0) {
            $max_errors = PHP_INT_MAX;
        } elseif ($max_errors === 0) {
            $max_errors = 1;
        }

        $this->maxErrors = $max_errors;
    }

    /**
     * @return int
     */
    public function maxErrors(): int
    {
        return $this->maxErrors;
    }

    /**
     * @param ValidationError $error
     * @return ErrorContainer
     */
    public function add(ValidationError $error): self
    {
        $this->errors[] = $error;
        return $this;
    }

    /**
     * @return ValidationError[]
     */
    public function all(): array
    {
        return $this->errors;
    }

    /**
     * @return ValidationError|null
     */
    public function first(): ?ValidationError
    {
        if (!$this->errors) {
            return null;
        }

        return reset($this->errors);
    }

    /**
     * @return bool
     */
    public function isFull(): bool
    {
        return count($this->errors) >= $this->maxErrors;
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return !$this->errors;
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return count($this->errors);
    }

    /**
     * @inheritDoc
     */
    public function current(): ?ValidationError
    {
        return current($this->errors) ?: null;
    }

    /**
     * @inheritDoc
     */
    public function next(): ?ValidationError
    {
        return next($this->errors) ?: null;
    }

    /**
     * @inheritDoc
     */
    public function key(): ?int
    {
        return key($this->errors);
    }

    /**
     * @inheritDoc
     */
    public function valid(): bool
    {
        return key($this->errors) !== null;
    }

    /**
     * @inheritDoc
     */
    public function rewind(): ?ValidationError
    {
        return reset($this->errors) ?: null;
    }
}