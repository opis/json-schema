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

namespace Opis\JsonSchema\Parsers;

use Opis\JsonSchema\{Info\ISchemaInfo, ISchema, Uri};

interface ISchemaParser
{
    /**
     * @param ISchemaInfo $info
     * @return ISchema
     */
    public function parseSchema(ISchemaInfo $info): ISchema;

    /**
     * @param object $schema
     * @param Uri $id
     * @param callable $handle_id
     * @param callable $handle_object
     * @param string|null $draft
     * @return ISchema|null
     */
    public function parseRootSchema(
        object $schema,
        Uri $id,
        callable $handle_id,
        callable $handle_object,
        ?string $draft = null
    ): ?ISchema;

    /**
     * @param string $schema
     * @return string|null
     */
    public function parseDraftVersion(string $schema): ?string;

    /**
     * @param object $schema
     * @param Uri|null $base
     * @return Uri|null
     */
    public function parseSchemaId(object $schema, ?Uri $base = null): ?Uri;

    /**
     * @param object $schema
     * @return string|null
     */
    public function parseSchemaDraft(object $schema): ?string;

    /**
     * @return string
     */
    public function defaultDraftVersion(): string;

    /**
     * @param string $draft
     * @return ISchemaParser
     */
    public function setDefaultDraftVersion(string $draft): self;

    /**
     * @param string $version
     * @return IDraft|null
     */
    public function draft(string $version): ?IDraft;

    /**
     * @param IDraft $draft
     * @return ISchemaParser
     */
    public function addDraft(IDraft $draft): self;

    /**
     * @param string $name
     * @param null $default
     * @return mixed
     */
    public function option(string $name, $default = null);

    /**
     * @param string $name
     * @param $value
     * @return ISchemaParser
     */
    public function setOption(string $name, $value): self;

    /**
     * @param array $options
     * @return ISchemaParser
     */
    public function setOptions(array $options): self;

    /**
     * @return array
     */
    public function getOptions(): array;

    /**
     * @param string $name
     * @param string|null $class
     * @return mixed
     */
    public function resolver(string $name, ?string $class = null);

    /**
     * @param string $name
     * @param $resolver
     * @return ISchemaParser
     */
    public function setResolver(string $name, $resolver): self;
}