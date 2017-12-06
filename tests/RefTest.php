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

use Opis\JsonSchema\Validator;

class RefTest extends \PHPUnit_Framework_TestCase
{
    use JsonValidatorTrait;

    public function testSimple()
    {
        $validator = $this->getValidator();

        $result = $validator->uriValidation((object)['name' => 'Name'], "schema:/simple-ref.json");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation((object)['name' => 'Name', 'age' => 23], "schema:/simple-ref.json");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation((object)['name' => 'name'], "schema:/simple-ref.json");
        $this->assertTrue($result->hasErrors());

        $result = $validator->uriValidation((object)['name' => 'Name', 'age' => 8.5], "schema:/simple-ref.json");
        $this->assertTrue($result->hasErrors());

        //

        $result = $validator->uriValidation(23, "schema:/simple-ref.json#age");
        $this->assertTrue($result->isValid());

    }

    public function testPointers()
    {
        $validator = $this->getValidator();

        $result = $validator->uriValidation(10, "schema:/pointer-ref.json#/properties/b/properties/c");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation(10, "schema:/pointer-ref.json#/definitions/c");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation(true, "schema:/pointer-ref.json#/properties/a");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation((object) [], "schema:/pointer-ref.json");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation((object) ["a" => true], "schema:/pointer-ref.json");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation((object) ["b" => (object)["c" => 10]], "schema:/pointer-ref.json");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation((object) ["a" => true, "b" => (object)["c" => 10]], "schema:/pointer-ref.json");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation((object) ["a" => 1, "b" => (object)["c" => 10]], "schema:/pointer-ref.json");
        $this->assertTrue($result->hasErrors());

        $result = $validator->uriValidation((object) ["b" => (object)["c" => 5]], "schema:/pointer-ref.json");
        $this->assertTrue($result->hasErrors());
    }

    public function testVars()
    {
        /** @var Validator $validator */
        $validator = $this->getValidator();

        $validator->setGlobalVars([
            'prefix' => 'valid'
        ]);

        $result = $validator->uriValidation((object)["age" => 18, "regionData" => "eu"], "schema:/var-ref.json");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation((object)["age" => 22, "regionData" => "us"], "schema:/var-ref.json");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation((object)["age" => 17, "regionData" => "eu"], "schema:/var-ref.json");
        $this->assertTrue($result->hasErrors());

        $result = $validator->uriValidation((object)["age" => 20, "regionData" => "us"], "schema:/var-ref.json");
        $this->assertTrue($result->hasErrors());

        $result = $validator->uriValidation((object)["age" => 25, "regionData" => "xx"], "schema:/var-ref.json");
        $this->assertTrue($result->hasErrors());

        $result = $validator->uriValidation((object)["age" => 25.5, "regionData" => "us"], "schema:/var-ref.json");
        $this->assertTrue($result->hasErrors());
    }

}