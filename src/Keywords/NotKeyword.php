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

class NotKeyword implements IKeyword
{
    use ErrorTrait;

    /** @var bool|object */
    protected $value;

    /**
     * @param bool|object $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * @inheritDoc
     */
    public function validate(IContext $context, ISchema $schema): ?IValidationError
    {
        if ($this->value === false) {
            return null;
        }
        if ($this->value === true) {
            return $this->error($schema, $context, 'not', "The data is never valid");
        }

        if (is_object($this->value) && !($this->value instanceof ISchema)) {
            $this->value = $context->loader()->loadObjectSchema($this->value);
        }

        $maxErrors = $context->maxErrors();
        $context->setMaxErrors(1);
        $error = $this->value->validate($context);
        $context->setMaxErrors($maxErrors);

        if ($error) {
            return null;
        }

        return $this->error($schema, $context, 'not', 'The data must not match schema');
    }
}