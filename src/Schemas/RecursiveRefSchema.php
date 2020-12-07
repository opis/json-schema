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

use Opis\JsonSchema\Info\SchemaInfo;
use Opis\JsonSchema\Errors\ValidationError;
use Opis\JsonSchema\{Exceptions\UnresolvedRefException, Schema, Uri, ValidationContext, Variables};

class RecursiveRefSchema extends AbstractRefSchema
{
    protected Uri $uri;

    /** @var bool|null|Schema */
    protected $resolved = false;

    public function __construct(
        SchemaInfo $info,
        Uri $uri,
        ?Variables $mapper,
        ?Variables $globals,
        ?array $slots = null
    ) {
        parent::__construct($info, $mapper, $globals, $slots);
        $this->uri = $uri;
    }

    /**
     * @inheritDoc
     */
    public function validate(ValidationContext $context): ?ValidationError
    {
        if ($this->resolved === false) {
            $this->resolved = $context->loader()->loadSchemaById($this->uri);
        }

        if ($this->resolved === null) {
            throw new UnresolvedRefException((string)$this->uri, $this, $context);
        }

        $new_context = $this->createContext($context, $this->mapper, $this->globals, $this->slots);

        if (!$this->hasRecursiveAnchor($this->resolved)) {
            return $this->resolved->validate($new_context);
        }


        $ok_sender = null;

        $ctx = $context;

        do {
            $sender = $ctx->sender();
            if (!$sender || !$this->hasRecursiveAnchor($sender)) {
                break;
            }
            if ($sender->info()->id()) {
                $ok_sender = $sender;
            } else {
                $ok_sender = $context->loader()->loadSchemaById($sender->info()->root());
            }
        } while ($ctx = $context->parent());

        if (!$ok_sender) {
            return $this->resolved->validate($new_context);
        }

        return $ok_sender->validate($new_context);
    }

    protected function hasRecursiveAnchor(?Schema $schema): bool
    {
        if (!$schema) {
            return false;
        }

        $info = $schema->info();

        if (!$info->isObject()) {
            return false;
        }

        $data = $info->data();

        if (!property_exists($data, '$recursiveAnchor')) {
            return false;
        }

        return $data->{'$recursiveAnchor'} === true;
    }
}