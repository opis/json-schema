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

use Opis\JsonSchema\{Errors\ValidationError,
    JsonPointer,
    Keyword,
    Schema,
    SchemaLoader,
    Uri,
    ValidationContext,
    Variables};

abstract class AbstractRefKeyword implements Keyword
{
    use ErrorTrait;

    protected string $keyword;
    protected ?Variables $mapper;
    protected ?Variables $globals;
    protected ?array $slots = null;
    protected ?Uri $lastRefUri = null;

    /**
     * @param Variables|null $mapper
     * @param Variables|null $globals
     * @param array|null $slots
     * @param string $keyword
     */
    protected function __construct(?Variables $mapper, ?Variables $globals, ?array $slots = null, string $keyword = '$ref')
    {
        $this->mapper = $mapper;
        $this->globals = $globals;
        $this->slots = $slots;
        $this->keyword = $keyword;
    }

    /**
     * @inheritDoc
     */
    public function validate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        if ($error = $this->doValidate($context, $schema)) {
            $uri = $this->lastRefUri;
            $this->lastRefUri = null;

            return $this->error($schema, $context, $this->keyword, 'The data must match {keyword}', [
                'keyword' => $this->keyword,
                'uri' => (string) $uri,
            ], $error);
        }

        $this->lastRefUri = null;

        return null;
    }


    abstract protected function doValidate(ValidationContext $context, Schema $schema): ?ValidationError;

    protected function setLastRefUri(?Uri $uri): void
    {
        $this->lastRefUri = $uri;
    }

    protected function setLastRefSchema(Schema $schema): void
    {
        $info = $schema->info();

        if ($info->id()) {
            $this->lastRefUri = $info->id();
        } else {
            $this->lastRefUri = Uri::merge(JsonPointer::pathToFragment($info->path()), $info->idBaseRoot());
        }
    }

    /**
     * @param ValidationContext $context
     * @param Schema $schema
     * @return ValidationContext
     */
    protected function createContext(ValidationContext $context, Schema $schema): ValidationContext
    {
        return $context->create($schema, $this->mapper, $this->globals, $this->slots);
    }

    /**
     * @param SchemaLoader $repo
     * @param JsonPointer $pointer
     * @param Uri $base
     * @param array|null $path
     * @return null|Schema
     */
    protected function resolvePointer(SchemaLoader $repo, JsonPointer $pointer,
        Uri $base, ?array $path = null): ?Schema
    {
        if ($pointer->isAbsolute()) {
            $path = (string)$pointer;
        } else {
            if ($pointer->hasFragment()) {
                return null;
            }

            $path = $path ? $pointer->absolutePath($path) : $pointer->path();
            if ($path === null) {
                return null;
            }

            $path = JsonPointer::pathToString($path);
        }

        return $repo->loadSchemaById(Uri::merge('#' . $path, $base));
    }
}