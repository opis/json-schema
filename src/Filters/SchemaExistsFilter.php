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

namespace Opis\JsonSchema\Filters;

use Opis\Uri\UriTemplate;
use Opis\JsonSchema\{ValidationContext, Filter, Schema, Uri};
use Opis\JsonSchema\Variables\VariablesContainer;

class SchemaExistsFilter implements Filter
{
    /**
     * @inheritDoc
     */
    public function validate(ValidationContext $context, Schema $schema, array $args = []): bool
    {
        $ref = $args['ref'] ?? $context->currentData();
        if (!is_string($ref)) {
            return false;
        }

        if (UriTemplate::isTemplate($ref)) {
            if (isset($args['vars']) && is_object($args['vars'])) {
                $vars = new VariablesContainer($args['vars'], false);
                $vars = $vars->resolve($context->rootData(), $context->currentDataPath());
                if (!is_array($vars)) {
                    $vars = (array)$vars;
                }
                $vars += $context->globals();
            } else {
                $vars = $context->globals();
            }

            $ref = (new UriTemplate($ref))->resolve($vars);

            unset($vars);
        }

        unset($args);

        return $this->refExists($ref, $context, $schema);
    }

    /**
     * @param string $ref
     * @param ValidationContext $context
     * @param Schema $schema
     * @return bool
     */
    protected function refExists(string $ref, ValidationContext $context, Schema $schema): bool
    {
        if ($ref === '') {
            return false;
        }

        if ($ref === '#') {
            return true;
        }

        $info = $schema->info();

        $id = Uri::merge($ref, $info->idBaseRoot(), true);

        if ($id === null) {
            return false;
        }

        return $context->loader()->loadSchemaById($id) !== null;
    }
}