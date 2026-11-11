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

use Opis\JsonSchema\Errors\ValidationError;
use Opis\JsonSchema\Exceptions\UnresolvedReferenceException;
use Opis\JsonSchema\{Schema, Uri, ValidationContext, Variables};

class DynamicRefKeyword extends AbstractRefKeyword
{
    protected Uri $uri;
    protected ?Schema $resolved = null;

    public function __construct(Uri $uri, ?Variables $mapper, ?Variables $globals, ?array $slots = null)
    {
        parent::__construct($mapper, $globals, $slots, '$dynamicRef');
        $this->uri = $uri;
    }

    protected function doValidate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        $loader = $context->loader();
        $resolved = $this->resolved ?? ($this->resolved = $loader->loadSchemaById($this->uri));

        if ($resolved === null) {
            throw new UnresolvedReferenceException((string)$this->uri, $schema, $context);
        }

        $anchor = $this->uri->fragment();
        if ($anchor !== null && $resolved->info()->isObject() &&
            $loader->parser()->parseDynamicAnchor($resolved->info()->data(), $resolved->info()->draft()) === $anchor) {
            // Search every resource on the dynamic path, keeping the outermost match.
            foreach ($context->dynamicScope() as $sender) {
                $target = $loader->loadSchemaById($sender->info()->idBaseRoot()->resolveRef('#' . $anchor));
                if ($target && $target->info()->isObject() &&
                    $loader->parser()->parseDynamicAnchor($target->info()->data(), $target->info()->draft()) === $anchor) {
                    $resolved = $target;
                }
            }
        }

        $this->setLastRefSchema($resolved);
        return $resolved->validate($this->createContext($context, $schema));
    }
}
