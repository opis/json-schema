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

use Opis\JsonSchema\{IContext, ISchema, Uri};
use Opis\JsonSchema\Info\ISchemaInfo;
use Opis\JsonSchema\Variables\IVariables;
use Opis\JsonSchema\Errors\IValidationError;
use Opis\JsonSchema\Exceptions\UnresolvedRefException;

class UriRefSchema extends AbstractRefSchema
{

    protected Uri $uri;

    /** @var bool|null|ISchema */
    protected $resolved = false;

    /**
     * @param ISchemaInfo $info
     * @param Uri $uri
     * @param IVariables|null $mapper
     * @param IVariables|null $globals
     * @param array|null $slots
     */
    public function __construct(ISchemaInfo $info, Uri $uri, ?IVariables $mapper,
                                ?IVariables $globals, ?array $slots = null)
    {
        parent::__construct($info, $mapper, $globals, $slots);
        $this->uri = $uri;
    }

    /**
     * @inheritDoc
     */
    public function validate(IContext $context): ?IValidationError
    {
        if ($this->resolved === false) {
            $this->resolved = $context->loader()->loadSchemaById($this->uri);
        }

        if ($this->resolved === null) {
            throw new UnresolvedRefException((string)$this->uri, $this, $context);
        }

        return $this->resolved->validate($this->createContext($context, $this->mapper, $this->globals, $this->slots));
    }
}