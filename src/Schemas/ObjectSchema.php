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

use Opis\JsonSchema\{Helper, IKeyword, IContext, IWrapperKeyword};
use Opis\JsonSchema\Info\ISchemaInfo;
use Opis\JsonSchema\Errors\IValidationError;
use Opis\JsonSchema\WrapperKeywords\CallbackWrapperKeyword;

class ObjectSchema extends AbstractSchema
{

    protected ?IWrapperKeyword $wrapper = null;

    /** @var IKeyword[]|null */
    protected ?array $before = null;

    /** @var IKeyword[]|null */
    protected ?array $after = null;

    /** @var IKeyword[][]|null */
    protected ?array $types = null;

    /**
     * @param ISchemaInfo $info
     * @param IWrapperKeyword|null $wrapper
     * @param IKeyword[][]|null $types
     * @param IKeyword[]|null $before
     * @param IKeyword[]|null $after
     */
    public function __construct(ISchemaInfo $info, ?IWrapperKeyword $wrapper, ?array $types, ?array $before, ?array $after)
    {
        parent::__construct($info);
        $this->types = $types;
        $this->before = $before;
        $this->after = $after;
        $this->wrapper = $wrapper;

        if ($wrapper) {
            while ($next = $wrapper->next()) {
                $wrapper = $next;
            }
            $wrapper->setNext(new CallbackWrapperKeyword([$this, 'doValidate']));
        }
    }

    /**
     * @inheritDoc
     */
    public function validate(IContext $context): ?IValidationError
    {
        $context->pushSharedObject();
        $error = $this->wrapper ? $this->wrapper->validate($context) : $this->doValidate($context);
        $context->popSharedObject();

        return $error;
    }

    /**
     * @internal
     * @param IContext $context
     * @return null|IValidationError
     */
    public function doValidate(IContext $context): ?IValidationError
    {
        if ($this->before && ($error = $this->applyKeywords($this->before, $context))) {
            return $error;
        }

        if ($this->types && ($type = $context->currentDataType())) {
            if (isset($this->types[$type]) && ($error = $this->applyKeywords($this->types[$type], $context))) {
                return $error;
            }

            if (($type = Helper::getJsonSuperType($type)) && isset($this->types[$type])) {
                if ($error = $this->applyKeywords($this->types[$type], $context)) {
                    return $error;
                }
            }

            unset($type);
        }

        if ($this->after && ($error = $this->applyKeywords($this->after, $context))) {
            return $error;
        }

        return null;
    }

    /**
     * @param IKeyword[] $keywords
     * @param IContext $context
     * @return IValidationError|null
     */
    protected function applyKeywords(array $keywords, IContext $context): ?IValidationError
    {
        foreach ($keywords as $keyword) {
            if ($error = $keyword->validate($context, $this)) {
                return $error;
            }
        }

        return null;
    }
}