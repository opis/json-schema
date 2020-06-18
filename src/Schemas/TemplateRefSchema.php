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

use Opis\JsonSchema\{Uri, Schema, UriTemplate, ValidationContext, JsonPointer};
use Opis\JsonSchema\Info\SchemaInfo;
use Opis\JsonSchema\SchemaLoader;
use Opis\JsonSchema\Variables\Variables;
use Opis\JsonSchema\Errors\ValidationError;
use Opis\JsonSchema\Exceptions\UnresolvedRefException;

class TemplateRefSchema extends AbstractRefSchema
{

    protected ?Variables $vars;

    protected UriTemplate $template;

    /** @var Schema[]|null[] */
    protected ?array $cached = [];

    protected ?Uri $baseUri = null;

    /**
     * @param SchemaInfo $info
     * @param UriTemplate $template
     * @param Variables|null $vars
     * @param Variables|null $mapper
     * @param Variables|null $globals
     * @param array|null $slots
     */
    public function __construct(SchemaInfo $info, UriTemplate $template,
                                ?Variables $vars, ?Variables $mapper,
                                ?Variables $globals, ?array $slots = null)
    {
        parent::__construct($info, $mapper, $globals, $slots);
        $this->template = $template;
        $this->vars = $vars;
        $this->baseUri = $this->resolveBaseUri($info);
    }

    /**
     * @inheritDoc
     */
    public function validate(ValidationContext $context): ?ValidationError
    {
        if ($this->vars) {
            $vars = $this->vars->resolve($context->rootData(), $context->currentDataPath());
            if (!is_array($vars)) {
                $vars = (array)$vars;
            }
            $vars += $context->globals();
        } else {
            $vars = $context->globals();
        }

        $ref = $this->template->resolve($vars);

        $key = isset($ref[32]) ? md5($ref) : $ref;

        if (!array_key_exists($key, $this->cached)) {
            $this->cached[$key] = $this->resolveRef($ref, $context->loader());
        }

        $schema = $this->cached[$key];
        unset($key);

        if (!$schema) {
            throw new UnresolvedRefException($ref, $this, $context);
        }

        return $schema->validate($this->createContext($context, $this->mapper, $this->globals, $this->slots));
    }

    /**
     * @param string $ref
     * @param SchemaLoader $repo
     * @return null|Schema
     */
    protected function resolveRef(string $ref, SchemaLoader $repo): ?Schema
    {
        if ($ref === '') {
            return null;
        }

        if ($ref === '#') {
            return $repo->loadSchemaById($this->baseUri);
        }

        // Check if is pointer
        if ($ref[0] === '#') {
            if ($pointer = JsonPointer::parse(substr($ref, 1))) {
                if ($pointer->isAbsolute()) {
                    return $this->resolvePointer($repo, $pointer, $this->baseUri);
                }
                unset($pointer);
            }
        } elseif ($pointer = JsonPointer::parse($ref)) {
            if ($pointer->isRelative()) {
                return $this->resolvePointer($repo, $pointer, $this->baseUri, $this->info->path());
            }
            unset($pointer);
        }

        $ref = Uri::merge($ref, $this->baseUri, true);

        if ($ref === null || !$ref->isAbsolute()) {
            return null;
        }

        return $repo->loadSchemaById($ref);
    }
}