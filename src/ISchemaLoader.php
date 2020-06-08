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

use Opis\JsonSchema\Parsers\ISchemaParser;
use Opis\JsonSchema\Resolvers\ISchemaResolver;

interface ISchemaLoader
{
    /**
     * @param object $data
     * @param null $id
     * @param string|null $draft
     * @return ISchema
     */
    public function loadObjectSchema(object $data, $id = null, ?string $draft = null): ISchema;

    /**
     * @param bool $data
     * @param null $id
     * @param string|null $draft
     * @return ISchema
     */
    public function loadBooleanSchema(bool $data, $id = null, ?string $draft = null): ISchema;

    /**
     * @param Uri $uri
     * @return null|ISchema
     */
    public function loadSchemaById(Uri $uri): ?ISchema;

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
     * @return ISchemaParser
     */
    public function parser(): ISchemaParser;

    /**
     * @param ISchemaParser $parser
     * @return $this
     */
    public function setParser(ISchemaParser $parser): self;

    /**
     * @return ISchemaResolver|null
     */
    public function resolver(): ?ISchemaResolver;

    /**
     * @param ISchemaResolver|null $resolver
     * @return $this
     */
    public function setResolver(?ISchemaResolver $resolver): self;
}