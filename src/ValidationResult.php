<?php
/* ============================================================================
 * Copyright 2021 Zindex Software
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

use Opis\JsonSchema\Errors\ValidationError;

class ValidationResult
{
    protected ?ValidationError $error;

    public function __construct(?ValidationError $error)
    {
        $this->error = $error;
    }

    public function error(): ?ValidationError
    {
        return $this->error;
    }

    public function isValid(): bool
    {
        return $this->error === null;
    }

    public function hasError(): bool
    {
        return $this->error !== null;
    }

    public function __toString(): string
    {
        if ($this->error) {
            return $this->error->message();
        }
        return '';
    }
}