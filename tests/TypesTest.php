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

class TypesTest extends \PHPUnit_Framework_TestCase
{
    use JsonValidatorTrait;

    public function testBoolean()
    {

        $validator = $this->getValidator();

        $result = $validator->uriValidation(true, "schema:/types.json#/definitions/boolean");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation(false, "schema:/types.json#/definitions/boolean");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation("true", "schema:/types.json#/definitions/boolean");
        $this->assertTrue($result->hasErrors());

        $result = $validator->uriValidation(1, "schema:/types.json#/definitions/boolean");
        $this->assertTrue($result->hasErrors());
    }

    public function testNull()
    {
        $validator = $this->getValidator();

        $result = $validator->uriValidation(null, "schema:/types.json#/definitions/null");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation(false, "schema:/types.json#/definitions/null");
        $this->assertTrue($result->hasErrors());

        $result = $validator->uriValidation("", "schema:/types.json#/definitions/null");
        $this->assertTrue($result->hasErrors());

        $result = $validator->uriValidation(0, "schema:/types.json#/definitions/null");
        $this->assertTrue($result->hasErrors());

        $result = $validator->uriValidation([], "schema:/types.json#/definitions/null");
        $this->assertTrue($result->hasErrors());
    }

    public function testInteger()
    {
        $validator = $this->getValidator();

        $result = $validator->uriValidation(5, "schema:/types.json#/definitions/integer");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation(-10, "schema:/types.json#/definitions/integer");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation(8.0, "schema:/types.json#/definitions/integer");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation(-6.0, "schema:/types.json#/definitions/integer");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation(5.5, "schema:/types.json#/definitions/integer");
        $this->assertTrue($result->hasErrors());

        $result = $validator->uriValidation(-8.1, "schema:/types.json#/definitions/integer");
        $this->assertTrue($result->hasErrors());

        $result = $validator->uriValidation("1", "schema:/types.json#/definitions/integer");
        $this->assertTrue($result->hasErrors());

        $result = $validator->uriValidation("0", "schema:/types.json#/definitions/integer");
        $this->assertTrue($result->hasErrors());

        $result = $validator->uriValidation("-2", "schema:/types.json#/definitions/integer");
        $this->assertTrue($result->hasErrors());

    }

    public function testNumber()
    {
        $validator = $this->getValidator();

        $result = $validator->uriValidation(5, "schema:/types.json#/definitions/number/simple");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation(-10, "schema:/types.json#/definitions/number/simple");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation(5.8, "schema:/types.json#/definitions/number/simple");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation(-8.5, "schema:/types.json#/definitions/number/simple");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation("0", "schema:/types.json#/definitions/number/simple");
        $this->assertTrue($result->hasErrors());

        $result = $validator->uriValidation("-2.5", "schema:/types.json#/definitions/number/simple");
        $this->assertTrue($result->hasErrors());

        // min, max

        $result = $validator->uriValidation(-0.8, "schema:/types.json#/definitions/number/interval");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation(5, "schema:/types.json#/definitions/number/interval");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation(7.5, "schema:/types.json#/definitions/number/interval");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation(7.51, "schema:/types.json#/definitions/number/interval");
        $this->assertTrue($result->hasErrors());

        $result = $validator->uriValidation(-0.81, "schema:/types.json#/definitions/number/interval");
        $this->assertTrue($result->hasErrors());

        // exclusive min/max

        $result = $validator->uriValidation(5, "schema:/types.json#/definitions/number/interval_exclusive");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation(-0.8, "schema:/types.json#/definitions/number/interval_exclusive");
        $this->assertTrue($result->hasErrors());

        $result = $validator->uriValidation(-0.81, "schema:/types.json#/definitions/number/interval_exclusive");
        $this->assertTrue($result->hasErrors());

        $result = $validator->uriValidation(7.5, "schema:/types.json#/definitions/number/interval_exclusive");
        $this->assertTrue($result->hasErrors());

        $result = $validator->uriValidation(7.51, "schema:/types.json#/definitions/number/interval_exclusive");
        $this->assertTrue($result->hasErrors());

        // multiple

        $result = $validator->uriValidation(1, "schema:/types.json#/definitions/number/multiple");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation(0.4, "schema:/types.json#/definitions/number/multiple");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation(-4.6, "schema:/types.json#/definitions/number/multiple");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation(1.1, "schema:/types.json#/definitions/number/multiple");
        $this->assertTrue($result->hasErrors());

        $result = $validator->uriValidation(-0.25, "schema:/types.json#/definitions/number/multiple");
        $this->assertTrue($result->hasErrors());

    }

    public function testString()
    {
        $validator = $this->getValidator();

        $result = $validator->uriValidation("some string", "schema:/types.json#/definitions/string/simple");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation(5.67, "schema:/types.json#/definitions/string/simple");
        $this->assertTrue($result->hasErrors());

        // format

        $result = $validator->uriValidation("name@example.com", "schema:/types.json#/definitions/string/format");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation("name(at)example.com", "schema:/types.json#/definitions/string/format");
        $this->assertTrue($result->hasErrors());

        // length

        $result = $validator->uriValidation("AA", "schema:/types.json#/definitions/string/length");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation("AAAAA", "schema:/types.json#/definitions/string/length");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation("", "schema:/types.json#/definitions/string/length");
        $this->assertTrue($result->hasErrors());

        $result = $validator->uriValidation("A", "schema:/types.json#/definitions/string/length");
        $this->assertTrue($result->hasErrors());

        $result = $validator->uriValidation(str_repeat('A', 11), "schema:/types.json#/definitions/string/length");
        $this->assertTrue($result->hasErrors());

        // pattern

        $result = $validator->uriValidation("abc", "schema:/types.json#/definitions/string/pattern");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation("Abc", "schema:/types.json#/definitions/string/pattern");
        $this->assertTrue($result->hasErrors());

        $result = $validator->uriValidation("1abc", "schema:/types.json#/definitions/string/pattern");
        $this->assertTrue($result->hasErrors());

        $result = $validator->uriValidation("", "schema:/types.json#/definitions/string/pattern");
        $this->assertTrue($result->hasErrors());

        $result = $validator->uriValidation("a b c", "schema:/types.json#/definitions/string/pattern");
        $this->assertTrue($result->hasErrors());

    }

    public function testArray()
    {
        $validator = $this->getValidator();

        $result = $validator->uriValidation([1, 2, 3], "schema:/types.json#/definitions/array/simple");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation([], "schema:/types.json#/definitions/array/simple");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation(["a" => 1], "schema:/types.json#/definitions/array/simple");
        $this->assertTrue($result->hasErrors());

        $result = $validator->uriValidation([2 => 2], "schema:/types.json#/definitions/array/simple");
        $this->assertTrue($result->hasErrors());

        // interval

        $result = $validator->uriValidation(["a", "b"], "schema:/types.json#/definitions/array/interval");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation(range(1, 10), "schema:/types.json#/definitions/array/interval");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation(["a"], "schema:/types.json#/definitions/array/interval");
        $this->assertTrue($result->hasErrors());

        $result = $validator->uriValidation(range(1, 11), "schema:/types.json#/definitions/array/interval");
        $this->assertTrue($result->hasErrors());

        // unique

        $result = $validator->uriValidation(["a", "b", "c", 1, "1"], "schema:/types.json#/definitions/array/unique");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation([null, 0, false, "", []], "schema:/types.json#/definitions/array/unique");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation([[1], [2], [3]], "schema:/types.json#/definitions/array/unique");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation([1, "a", 1.0], "schema:/types.json#/definitions/array/unique");
        $this->assertTrue($result->hasErrors());

        $result = $validator->uriValidation(["A", "a", "A"], "schema:/types.json#/definitions/array/unique");
        $this->assertTrue($result->hasErrors());

        $result = $validator->uriValidation([[[[1]]], [[[1.0]]]], "schema:/types.json#/definitions/array/unique");
        $this->assertTrue($result->hasErrors());

        // contains

        $result = $validator->uriValidation([1, "2", "you found me", "other"], "schema:/types.json#/definitions/array/contains");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation([1, "2", "you found ME", "other"], "schema:/types.json#/definitions/array/contains");
        $this->assertTrue($result->hasErrors());

        $result = $validator->uriValidation([1, "2", "other"], "schema:/types.json#/definitions/array/contains");
        $this->assertTrue($result->hasErrors());

        $result = $validator->uriValidation(["key" => "you found me"], "schema:/types.json#/definitions/array/contains");
        $this->assertTrue($result->hasErrors());

        // items object

        $result = $validator->uriValidation([-3, -2.0, "-1", "", 1, "2", 3, "4", 5.0], "schema:/types.json#/definitions/array/items_object");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation([1, 2], "schema:/types.json#/definitions/array/items_object");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation([], "schema:/types.json#/definitions/array/items_object");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation(["a", "b"], "schema:/types.json#/definitions/array/items_object");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation(["a", 1, 5.123], "schema:/types.json#/definitions/array/items_object");
        $this->assertTrue($result->hasErrors());

        $result = $validator->uriValidation(["a", 1, null], "schema:/types.json#/definitions/array/items_object");
        $this->assertTrue($result->hasErrors());

        $result = $validator->uriValidation(["a", 1, false], "schema:/types.json#/definitions/array/items_object");
        $this->assertTrue($result->hasErrors());

        // items array


        $result = $validator->uriValidation([], "schema:/types.json#/definitions/array/items_array");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation([-3], "schema:/types.json#/definitions/array/items_array");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation([-3, "ok"], "schema:/types.json#/definitions/array/items_array");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation([-3, "ok", 4, "t"], "schema:/types.json#/definitions/array/items_array");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation([-3.2, "ok", null, false, []], "schema:/types.json#/definitions/array/items_array");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation(["a", 3], "schema:/types.json#/definitions/array/items_array");
        $this->assertTrue($result->hasErrors());

        // items additional

        $result = $validator->uriValidation([5.5], "schema:/types.json#/definitions/array/items_additional");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation([-3, "ok"], "schema:/types.json#/definitions/array/items_additional");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation([-3, "ok", null, null, null], "schema:/types.json#/definitions/array/items_additional");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation([-3, "ok", 1, null], "schema:/types.json#/definitions/array/items_additional");
        $this->assertTrue($result->hasErrors());

        $result = $validator->uriValidation([-3, "ok", null, null, -3], "schema:/types.json#/definitions/array/items_additional");
        $this->assertTrue($result->hasErrors());

    }

    public function testObject()
    {
        $validator = $this->getValidator();

        $result = $validator->uriValidation((object)[], "schema:/types.json#/definitions/object/simple");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation(null, "schema:/types.json#/definitions/object/simple");
        $this->assertTrue($result->hasErrors());

        // interval

        $result = $validator->uriValidation((object)["p1" => 1, "p2" => 2], "schema:/types.json#/definitions/object/interval");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation((object)["p1" => 1, "p2" => 2, "p3" => 3], "schema:/types.json#/definitions/object/interval");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation((object)["p1" => 1], "schema:/types.json#/definitions/object/interval");
        $this->assertTrue($result->hasErrors());

        $result = $validator->uriValidation((object)["p1" => 1, "p2" => 2, "p3" => 3, "p4" => 4], "schema:/types.json#/definitions/object/interval");
        $this->assertTrue($result->hasErrors());

        // required

        $result = $validator->uriValidation((object)["p1" => 1, "p2" => 2], "schema:/types.json#/definitions/object/required");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation((object)["p1" => 1, "p2" => null, "p3" => 2], "schema:/types.json#/definitions/object/required");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation((object)["p1" => 1], "schema:/types.json#/definitions/object/required");
        $this->assertTrue($result->hasErrors());

        // props

        $result = $validator->uriValidation((object)[], "schema:/types.json#/definitions/object/props");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation((object)["p1" => 1], "schema:/types.json#/definitions/object/props");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation((object)["p2" => ""], "schema:/types.json#/definitions/object/props");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation((object)["p1" => 1, "p2" => "str"], "schema:/types.json#/definitions/object/props");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation((object)["p1" => 1, "p2" => "str", "p3" => 2.5], "schema:/types.json#/definitions/object/props");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation((object)["p1" => "str", "p2" => 1], "schema:/types.json#/definitions/object/props");
        $this->assertTrue($result->hasErrors());

        $result = $validator->uriValidation((object)["p2" => 1], "schema:/types.json#/definitions/object/props");
        $this->assertTrue($result->hasErrors());

        $result = $validator->uriValidation((object)["p1" => "str"], "schema:/types.json#/definitions/object/props");
        $this->assertTrue($result->hasErrors());

        // props additional

        $result = $validator->uriValidation((object)[], "schema:/types.json#/definitions/object/props_additional");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation((object)["p1" => 1], "schema:/types.json#/definitions/object/props_additional");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation((object)["p2" => ""], "schema:/types.json#/definitions/object/props_additional");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation((object)["p1" => 1, "p2" => "str"], "schema:/types.json#/definitions/object/props_additional");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation((object)["p1" => 1, "p2" => "str", "p5" => null], "schema:/types.json#/definitions/object/props_additional");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation((object)["other" => null], "schema:/types.json#/definitions/object/props_additional");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation((object)["p1" => 1, "p2" => "str", "other" => false], "schema:/types.json#/definitions/object/props_additional");
        $this->assertTrue($result->hasErrors());

        // pattern (propertyNames)

        $result = $validator->uriValidation((object)[], "schema:/types.json#/definitions/object/pattern");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation((object)["prop" => 1], "schema:/types.json#/definitions/object/pattern");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation((object)["p1" => 5], "schema:/types.json#/definitions/object/pattern");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation((object)["p8" => "str"], "schema:/types.json#/definitions/object/pattern");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation((object)["p5" => -3.2, "p6" => "str"], "schema:/types.json#/definitions/object/pattern");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation((object)["p34" => false], "schema:/types.json#/definitions/object/pattern");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation((object)["p3" => "5"], "schema:/types.json#/definitions/object/pattern");
        $this->assertTrue($result->hasErrors());

        $result = $validator->uriValidation((object)["p0" => 1], "schema:/types.json#/definitions/object/pattern");
        $this->assertTrue($result->hasErrors());

        // dep

        $result = $validator->uriValidation((object)[], "schema:/types.json#/definitions/object/dep");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation((object)['e' => 1], "schema:/types.json#/definitions/object/dep");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation((object)['a' => 1, 'b' => 2], "schema:/types.json#/definitions/object/dep");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation((object)['c' => 1, 'd' => 2], "schema:/types.json#/definitions/object/dep");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation((object)['b' => 1, 'd' => 2], "schema:/types.json#/definitions/object/dep");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation((object)['e' => 'str'], "schema:/types.json#/definitions/object/dep");
        $this->assertTrue($result->hasErrors());

        $result = $validator->uriValidation((object)['a' => 'str'], "schema:/types.json#/definitions/object/dep");
        $this->assertTrue($result->hasErrors());

        $result = $validator->uriValidation((object)['c' => 1], "schema:/types.json#/definitions/object/dep");
        $this->assertTrue($result->hasErrors());

    }

    public function testCombined()
    {
        $validator = $this->getValidator();

        $result = $validator->uriValidation(true, "schema:/types.json#/definitions/combined");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation(0.8, "schema:/types.json#/definitions/combined");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation("str", "schema:/types.json#/definitions/combined");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation([1, 2, 3], "schema:/types.json#/definitions/combined");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation((object)['a' => null], "schema:/types.json#/definitions/combined");
        $this->assertTrue($result->isValid());

        $result = $validator->uriValidation(null, "schema:/types.json#/definitions/combined");
        $this->assertTrue($result->hasErrors());

        $result = $validator->uriValidation(1.1, "schema:/types.json#/definitions/combined");
        $this->assertTrue($result->hasErrors());

        $result = $validator->uriValidation("a", "schema:/types.json#/definitions/combined");
        $this->assertTrue($result->hasErrors());

        $result = $validator->uriValidation([1, 2], "schema:/types.json#/definitions/combined");
        $this->assertTrue($result->hasErrors());

        $result = $validator->uriValidation((object)['b' => ''], "schema:/types.json#/definitions/combined");
        $this->assertTrue($result->hasErrors());

    }

}