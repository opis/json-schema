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

namespace Opis\JsonSchema\KeywordValidators;

use Opis\JsonSchema\{ValidationContext, Pragma};
use Opis\JsonSchema\Errors\ValidationError;

final class PragmaKeywordValidator extends AbstractKeywordValidator
{
    /** @var Pragma[] */
    protected array $pragmas = [];

    /**
     * @param Pragma[] $pragmas
     */
    public function __construct(array $pragmas)
    {
        $this->pragmas = $pragmas;
    }

    /**
     * @inheritDoc
     */
    public function validate(ValidationContext $context): ?ValidationError
    {
        if (!$this->next) {
            return null;
        }

        if (!$this->pragmas) {
            return $this->next->validate($context);
        }

        $data = [];

        foreach ($this->pragmas as $key => $handler) {
            $data[$key] = $handler->enter($context);
        }

        $error = $this->next->validate($context);

        foreach (array_reverse($this->pragmas, true) as $key => $handler) {
            $handler->leave($context, $data[$key] ?? null);
        }

        return $error;
    }
}