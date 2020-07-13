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

namespace Opis\JsonSchema\Schemas;

use Opis\JsonSchema\{ValidationContext, Schema, SchemaLoader, JsonPointer, Uri};
use Opis\JsonSchema\Info\SchemaInfo;
use Opis\JsonSchema\Variables;

abstract class AbstractRefSchema extends AbstractSchema
{

    protected ?Variables $mapper;

    protected ?Variables $globals;

    protected ?array $slots = null;

    /**
     * @param SchemaInfo $info
     * @param Variables|null $mapper
     * @param Variables|null $globals
     * @param array|null $slots
     */
    public function __construct(SchemaInfo $info, ?Variables $mapper, ?Variables $globals, ?array $slots = null)
    {
        parent::__construct($info);
        $this->mapper = $mapper;
        $this->globals = $globals;
        $this->slots = $slots;
    }

    /**
     * @param ValidationContext $context
     * @param null|Variables $mapper
     * @param null|Variables $globals
     * @param null|array $slots
     * @return ValidationContext
     */
    protected function createContext(
        ValidationContext $context,
        ?Variables $mapper = null,
        ?Variables $globals = null,
        ?array $slots = null
    ): ValidationContext
    {
        if ($globals) {
            $globals = $globals->resolve($context->rootData(), $context->currentDataPath());
            if (!is_array($globals)) {
                $globals = (array)$globals;
            }
            $globals += $context->globals();
        } else {
            $globals = $context->globals();
        }

        if ($mapper) {
            $data = $mapper->resolve($context->rootData(), $context->currentDataPath());
        } else {
            $data = $context->currentData();
        }

        return $context->newInstance($data, $globals, $slots);
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

    /**
     * @param SchemaInfo $info
     * @return Uri|null
     */
    protected function resolveBaseUri(SchemaInfo $info): ?Uri
    {
        return $info->id() ?? $info->base() ?? $info->root();
    }
}