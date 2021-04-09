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

namespace Opis\JsonSchema\Exceptions;

use Opis\JsonSchema\{ValidationContext, Schema};

class UnresolvedReferenceException extends UnresolvedException
{

    protected string $ref;

    /**
     * @param string $ref
     * @param Schema $schema
     * @param ValidationContext $context
     */
    public function __construct(string $ref, Schema $schema, ValidationContext $context)
    {
        parent::__construct("Unresolved reference: {$ref}", $schema, $context);
        $this->ref = $ref;
    }

    /**
     * @return string
     */
    public function getRef(): string
    {
        return $this->ref;
    }
}