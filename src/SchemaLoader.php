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

namespace Opis\JsonSchema;

use Opis\JsonSchema\Parsers\SchemaParser;
use Opis\JsonSchema\Resolvers\SchemaResolver;

interface SchemaLoader
{
    /**
     * @param object $data
     * @param null $id
     * @param string|null $draft
     * @return Schema
     */
    public function loadObjectSchema(object $data, $id = null, ?string $draft = null): Schema;

    /**
     * @param bool $data
     * @param null $id
     * @param string|null $draft
     * @return Schema
     */
    public function loadBooleanSchema(bool $data, $id = null, ?string $draft = null): Schema;

    /**
     * @param Uri $uri
     * @return null|Schema
     */
    public function loadSchemaById(Uri $uri): ?Schema;

    /**
     * @return Uri|null
     */
    public function baseUri(): ?Uri;

    /**
     * @param Uri|null $uri
     * @return $this
     */
    public function setBaseUri(?Uri $uri): self;

    /**
     * @return SchemaParser
     */
    public function parser(): SchemaParser;

    /**
     * @param SchemaParser $parser
     * @return $this
     */
    public function setParser(SchemaParser $parser): self;

    /**
     * @return SchemaResolver|null
     */
    public function resolver(): ?SchemaResolver;

    /**
     * @param SchemaResolver|null $resolver
     * @return $this
     */
    public function setResolver(?SchemaResolver $resolver): self;
}