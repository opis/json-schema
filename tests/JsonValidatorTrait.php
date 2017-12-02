<?php
/* ===========================================================================
 * Copyright 2014-2017 The Opis Project
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

namespace Opis\JsonSchema\Test;


use Opis\JsonSchema\ISchemaLoader;
use Opis\JsonSchema\IValidator;
use Opis\JsonSchema\Loaders\File;
use Opis\JsonSchema\Validator;

trait JsonValidatorTrait
{

    protected $validator = null;

    protected function getValidator(): IValidator
    {
        if (!$this->validator) {
            $this->validator = $this->createValidator();
        }
        return $this->validator;
    }

    protected function createValidator(ISchemaLoader $loader = null, bool $use_default = true): IValidator
    {
        if ($loader === null) {
            $loader = new File(__DIR__ . '/schemas', 'schema:');
        }
        return new Validator(null, $loader, null, null, $use_default);
    }

}