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

class BasicTest extends \PHPUnit_Framework_TestCase
{
    use JsonValidatorTrait;

    public function testBasics()
    {
        $validator = $this->getValidator();

        $result = $validator->dataValidation("abc", (object)["minLength" => 3]);
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation(10, "schema:/basic.json#/definitions/constant");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation("a", "schema:/basic.json#/definitions/constant");
        $this->assertTrue($result->hasErrors());

        $result = $validator->uriValidation("b", "schema:/basic.json#/definitions/enumeration");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation(10, "schema:/basic.json#/definitions/enumeration");
        $this->assertTrue($result->hasErrors());

        $result = $validator->uriValidation("always fail", "schema:/basic.json#/definitions/false");
        $this->assertTrue($result->hasErrors());

        $result = $validator->uriValidation([[1], ["a"], (object)["x" => "always ok"]], "schema:/basic.json#/definitions/true");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation("for empty schema", "schema:/basic.json#/definitions/empty");
        $this->assertTrue($result->isValid());
    }

    public function testDefault()
    {
        /** @var Validator $validator */
        $validator = $this->getValidator();

        $result = $validator->uriValidation((object)["a" => "aaa"], "schema:/basic.json#/definitions/def");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation((object)[], "schema:/basic.json#/definitions/def");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation((object)["a" => 5], "schema:/basic.json#/definitions/def");
        $this->assertTrue($result->hasErrors());

        $validator->defaultSupport(false);
        $result = $validator->uriValidation((object)[], "schema:/basic.json#/definitions/def");
        $this->assertTrue($result->hasErrors());
        $validator->defaultSupport(true);
    }

    public function testConditionals()
    {
        $validator = $this->getValidator();

        $result = $validator->uriValidation(10, "schema:/basic.json#/definitions/cond/negation");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation("x", "schema:/basic.json#/definitions/cond/negation");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation("a", "schema:/basic.json#/definitions/cond/negation");
        $this->assertTrue($result->hasErrors());

        $result = $validator->uriValidation("b", "schema:/basic.json#/definitions/cond/negation");
        $this->assertTrue($result->hasErrors());

        // if-then

        $result = $validator->uriValidation(10, "schema:/basic.json#/definitions/cond/if-then");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation(10.5, "schema:/basic.json#/definitions/cond/if-then");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation("str", "schema:/basic.json#/definitions/cond/if-then");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation(5, "schema:/basic.json#/definitions/cond/if-then");
        $this->assertTrue($result->hasErrors());

        // if-else

        $result = $validator->uriValidation(10, "schema:/basic.json#/definitions/cond/if-else");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation(5, "schema:/basic.json#/definitions/cond/if-else");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation(5.8, "schema:/basic.json#/definitions/cond/if-else");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation("str", "schema:/basic.json#/definitions/cond/if-else");
        $this->assertTrue($result->hasErrors());

        // if-then-else
        $result = $validator->uriValidation(10, "schema:/basic.json#/definitions/cond/if-then-else");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation(5.8, "schema:/basic.json#/definitions/cond/if-then-else");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation(5, "schema:/basic.json#/definitions/cond/if-then-else");
        $this->assertTrue($result->hasErrors());

        $result = $validator->uriValidation(3.2, "schema:/basic.json#/definitions/cond/if-then-else");
        $this->assertTrue($result->hasErrors());

        $result = $validator->uriValidation("str", "schema:/basic.json#/definitions/cond/if-then-else");
        $this->assertTrue($result->hasErrors());

        // all

        $result = $validator->uriValidation(1, "schema:/basic.json#/definitions/cond/all");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation(1.0, "schema:/basic.json#/definitions/cond/all");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation(2, "schema:/basic.json#/definitions/cond/all");
        $this->assertTrue($result->hasErrors());

        $result = $validator->uriValidation(3, "schema:/basic.json#/definitions/cond/all");
        $this->assertTrue($result->hasErrors());

        $result = $validator->uriValidation("str", "schema:/basic.json#/definitions/cond/all");
        $this->assertTrue($result->hasErrors());

        // any
        $result = $validator->uriValidation(1, "schema:/basic.json#/definitions/cond/any");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation("127.0.0.1", "schema:/basic.json#/definitions/cond/any");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation("x", "schema:/basic.json#/definitions/cond/any");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation("y", "schema:/basic.json#/definitions/cond/any");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation(5.5, "schema:/basic.json#/definitions/cond/any");
        $this->assertTrue($result->hasErrors());

        $result = $validator->uriValidation("z", "schema:/basic.json#/definitions/cond/any");
        $this->assertTrue($result->hasErrors());

        // one

        $result = $validator->uriValidation(1, "schema:/basic.json#/definitions/cond/one");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation(-2.0, "schema:/basic.json#/definitions/cond/one");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation(true, "schema:/basic.json#/definitions/cond/one");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation(false, "schema:/basic.json#/definitions/cond/one");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation([1, 2], "schema:/basic.json#/definitions/cond/one");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation(["a"], "schema:/basic.json#/definitions/cond/one");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation([1, 2, "a"], "schema:/basic.json#/definitions/cond/one");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation("str", "schema:/basic.json#/definitions/cond/one");
        $this->assertTrue($result->hasErrors());

        $result = $validator->uriValidation(["a", "b"], "schema:/basic.json#/definitions/cond/one");
        $this->assertTrue($result->hasErrors());

    }
}