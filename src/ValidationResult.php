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

final class ValidationResult
{

    /** @var int */
    protected $maxErrors;

    /** @var array */
    protected $errors = [];

    /** @var int */
    protected $totalErrors = 0;

    /**
     * ErrorBag constructor.
     * @param int $max_errors
     */
    public function __construct(int $max_errors = 1)
    {
        if ($max_errors < 0) {
            $max_errors = PHP_INT_MAX;
        }
        elseif ($max_errors === 0) {
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
     * @return int
     */
    public function totalErrors(): int
    {
        return $this->totalErrors;
    }

    /**
     * @return bool
     */
    public function isFull(): bool
    {
        return $this->maxErrors <= $this->totalErrors;
    }

    /**
     * @return bool
     */
    public function hasErrors(): bool
    {
        return $this->totalErrors > 0;
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->totalErrors === 0;
    }

    /**
     * @return ValidationError[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @return ValidationError|null
     */
    public function getFirstError()
    {
        if ($this->errors) {
            return reset($this->errors);
        }
        return null;
    }

    /**
     * @param ValidationError $error
     * @return ValidationResult
     */
    public function addError(ValidationError $error): self
    {
        $this->errors[] = $error;
        $this->totalErrors += $error->subErrorsCount() + 1;
        return $this;
    }

    /**
     * Clears all errors
     * @return ValidationResult
     */
    public function clear(): self
    {
        $this->errors = [];
        $this->totalErrors = 0;
        return $this;
    }

    /**
     * @return ValidationResult
     */
    public function createByDiff(): self
    {
        $max = $this->maxErrors - $this->totalErrors;
        if ($max < 1) {
            $max = 1;
        }
        return new self($max);
    }
}