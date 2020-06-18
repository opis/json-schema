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

use RuntimeException;
use Opis\JsonSchema\Uri;

class DuplicateSchemaIdException extends RuntimeException implements SchemaException
{

    protected Uri $id;

    protected ?object $data = null;

    /**
     * DuplicateSchemaIdException constructor.
     * @param Uri $id
     * @param object|null $data
     */
    public function __construct(Uri $id, ?object $data = null)
    {
        parent::__construct("Duplicate schema id: {$id}", 0);
        $this->id = $id;
        $this->data = $data;
    }

    /**
     * @return null|object
     */
    public function getData(): ?object
    {
        return $this->data;
    }

    /**
     * @return Uri
     */
    public function getId(): Uri
    {
        return $this->id;
    }
}