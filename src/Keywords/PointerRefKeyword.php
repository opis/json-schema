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

namespace Opis\JsonSchema\Keywords;

use Opis\JsonSchema\Errors\ValidationError;
use Opis\JsonSchema\Exceptions\UnresolvedReferenceException;
use Opis\JsonSchema\{JsonPointer, Schema, ValidationContext, Variables};

class PointerRefKeyword extends AbstractRefKeyword
{
    protected JsonPointer $pointer;
    /** @var bool|null|Schema */
    protected $resolved = false;

    public function __construct(
        JsonPointer $pointer,
        ?Variables $mapper,
        ?Variables $globals,
        ?array $slots = null,
        string $keyword = '$ref'
    ) {
        parent::__construct($mapper, $globals, $slots, $keyword);
        $this->pointer = $pointer;
    }

    protected function doValidate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        if ($this->resolved === false) {
            $info = $schema->info();
            $this->resolved = $this->resolvePointer($context->loader(), $this->pointer, $info->idBaseRoot(), $info->path());
        }

        if ($this->resolved === null) {
            throw new UnresolvedReferenceException((string)$this->pointer, $schema, $context);
        }

        return $this->resolved->validate($this->createContext($context, $schema));
    }
}