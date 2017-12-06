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

use Opis\JsonSchema\FilterContainer;
use Opis\JsonSchema\IFilter;
use Opis\JsonSchema\Validator;

class FilterTest extends \PHPUnit_Framework_TestCase
{
    use JsonValidatorTrait;

    public function testFilters()
    {
        /** @var Validator $validator */
        $validator = $this->getValidator();

        $fc = new FilterContainer();

        $fc->add('string', 'oddLen', new class implements IFilter {
            /**
             * @inheritDoc
             */
            public function validate($data, array $args): bool
            {
                return strlen($data) % 2 === 1;
            }
        });

        $fc->add('number', 'modulo', new  class implements IFilter {
            /**
             * @inheritDoc
             */
            public function validate($data, array $args): bool
            {
                return $data % $args['divisor'] == $args['reminder'];
            }
        });


        $validator->setFilters($fc);

        // simple

        $result = $validator->uriValidation("a", "schema:/filter.json#/definitions/simple");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation("AbC", "schema:/filter.json#/definitions/simple");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation("ab", "schema:/filter.json#/definitions/simple");
        $this->assertTrue($result->hasErrors());

        $result = $validator->uriValidation("", "schema:/filter.json#/definitions/simple");
        $this->assertTrue($result->hasErrors());

        $result = $validator->uriValidation(52, "schema:/filter.json#/definitions/simple");
        $this->assertTrue($result->hasErrors());

        // vars

        $result = $validator->uriValidation(5, "schema:/filter.json#/definitions/vars");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation(2, "schema:/filter.json#/definitions/vars");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation(2.5, "schema:/filter.json#/definitions/vars");
        $this->assertTrue($result->hasErrors());

        $result = $validator->uriValidation(6, "schema:/filter.json#/definitions/vars");
        $this->assertTrue($result->hasErrors());

        $result = $validator->uriValidation(7, "schema:/filter.json#/definitions/vars");
        $this->assertTrue($result->hasErrors());

        // multi

        $result = $validator->uriValidation(5, "schema:/filter.json#/definitions/multi");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation(11, "schema:/filter.json#/definitions/multi");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation(6, "schema:/filter.json#/definitions/multi");
        $this->assertTrue($result->hasErrors());

        $result = $validator->uriValidation(7, "schema:/filter.json#/definitions/multi");
        $this->assertTrue($result->hasErrors());

        // str

        $result = $validator->uriValidation("a", "schema:/filter.json#/definitions/str");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation("ab", "schema:/filter.json#/definitions/str");
        $this->assertTrue($result->hasErrors());

        // globals

        $validator->setGlobalVars([
            'divisor' => 2,
            'reminder' => 0
        ]);

        $result = $validator->uriValidation(6, "schema:/filter.json#/definitions/globals");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation(12, "schema:/filter.json#/definitions/globals");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation(2, "schema:/filter.json#/definitions/globals");
        $this->assertTrue($result->hasErrors());

        $result = $validator->uriValidation(8, "schema:/filter.json#/definitions/globals");
        $this->assertTrue($result->hasErrors());


    }
}