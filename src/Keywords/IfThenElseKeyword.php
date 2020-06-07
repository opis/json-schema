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

use Opis\JsonSchema\{
    IContext,
    IKeyword,
    ISchema
};
use Opis\JsonSchema\Errors\IValidationError;

class IfThenElseKeyword implements IKeyword
{
    use ErrorTrait;

    /** @var bool|object */
    protected $if;

    /** @var bool|object */
    protected $then;

    /** @var bool|object */
    protected $else;

    /**
     * @param bool|object $if
     * @param bool|object $then
     * @param bool|object $else
     */
    public function __construct($if, $then, $else)
    {
        $this->if = $if;
        $this->then = $then;
        $this->else = $else;
    }

    /**
     * @inheritDoc
     */
    public function validate(IContext $context, ISchema $schema): ?IValidationError
    {
        if ($this->if === true) {
            return $this->validateBranch('then', $context, $schema);
        } elseif ($this->if === false) {
            return $this->validateBranch('else', $context, $schema);
        }

        if (is_object($this->if) && !($this->if instanceof ISchema)) {
            $this->if = $context->loader()->loadObjectSchema($this->if);
        }

        if ($this->if->validate($context)) {
            return $this->validateBranch('else', $context, $schema);
        }

        return $this->validateBranch('then', $context, $schema);
    }

    /**
     * @param string $branch
     * @param IContext $context
     * @param ISchema $schema
     * @return IValidationError|null
     */
    protected function validateBranch(string $branch, IContext $context, ISchema $schema): ?IValidationError
    {
        $value = $this->{$branch};

        if ($value === true) {
            return null;
        } elseif ($value === false) {
            return $this->error($schema, $context, $branch, "The data is never valid on '{$branch}' branch");
        }

        if (is_object($value) && !($value instanceof ISchema)) {
            $value = $this->{$branch} = $context->loader()->loadObjectSchema($value);
        }

        if ($error = $value->validate($context)) {
            return $this->error($schema, $context, $branch, "The data is not valid on '{$branch}' branch", [], $error);
        }

        return null;
    }
}