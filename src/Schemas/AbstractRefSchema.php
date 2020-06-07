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

use Opis\JsonSchema\{IContext, ISchema, ISchemaLoader, JsonPointer, Uri};
use Opis\JsonSchema\Info\ISchemaInfo;
use Opis\JsonSchema\Variables\IVariables;

abstract class AbstractRefSchema extends AbstractSchema
{

    protected ?IVariables $mapper;

    protected ?IVariables $globals;

    protected ?array $slots = null;

    /**
     * @param ISchemaInfo $info
     * @param IVariables|null $mapper
     * @param IVariables|null $globals
     * @param array|null $slots
     */
    public function __construct(ISchemaInfo $info, ?IVariables $mapper, ?IVariables $globals, ?array $slots = null)
    {
        parent::__construct($info);
        $this->mapper = $mapper;
        $this->globals = $globals;
        $this->slots = $slots;
    }

    /**
     * @param IContext $context
     * @param null|IVariables $mapper
     * @param null|IVariables $globals
     * @param null|array $slots
     * @return IContext
     */
    protected function createContext(
        IContext $context,
        ?IVariables $mapper = null,
        ?IVariables $globals = null,
        ?array $slots = null
    ): IContext
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
     * @param ISchemaLoader $repo
     * @param JsonPointer $pointer
     * @param Uri $base
     * @param array|null $path
     * @return null|ISchema
     */
    protected function resolvePointer(ISchemaLoader $repo, JsonPointer $pointer,
                                      Uri $base, ?array $path = null): ?ISchema
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
     * @param ISchemaInfo $info
     * @return Uri|null
     */
    protected function resolveBaseUri(ISchemaInfo $info): ?Uri
    {
        return $info->id() ?? $info->base() ?? $info->root();
    }
}