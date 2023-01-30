<?php

namespace Opis\JsonSchema\Test;

use Opis\JsonSchema\Helper;
use PHPUnit\Framework\TestCase;

class HelperTest extends TestCase
{
    public function testConvertAssocArrayToObject()
    {
        $object = Helper::convertAssocArrayToObject(['a' => ['b' => ['c' => 'd']]]);
        $this->assertSame('d', $object->a->b->c);
    }

    public function testConvertAssocArrayWithObjectToObject()
    {
        $object = Helper::convertAssocArrayToObject(['a' => (object) ['b' => ['c' => 'd']]]);
        $this->assertSame('d', $object->a->b->c);
    }

    public function testConvertAssocArrayWithIndexedArrayToObject()
    {
        $object = Helper::convertAssocArrayToObject(['a' => ['b', ['c' => 'd']]]);
        $this->assertSame('b', $object->a[0]);
        $this->assertSame('d', $object->a[1]->c);
    }

      public function testConvertAssocArrayToObjectWithScalar()
    {
        $this->assertNull(Helper::convertAssocArrayToObject(null));
        $this->assertSame(2, Helper::convertAssocArrayToObject(2));
        $this->assertSame('foo', Helper::convertAssocArrayToObject('foo'));
        $this->assertSame(true, Helper::convertAssocArrayToObject(true));
    }

    /**
     * @dataProvider multipleOfData
     */
    public function testIsMultipleOf($x, $y, bool $expectedTrue = true)
    {
        if ($expectedTrue) {
            $this->assertTrue(Helper::isMultipleOf($x, $y), sprintf('%s wasn\'t detected as a multiple of %s but it should', $x, $y));
            return;
        }

        $this->assertFalse(Helper::isMultipleOf($x, $y), sprintf('%s was detected as a multiple of %s but it shouldn\'t', $x, $y));
    }

    public function multipleOfData()
    {
        yield 'number 9007199254740991.0, divisor 1' => [9007199254740991.0, 1];
        yield 'number 9007199254740996.0, divisor 1' => [9007199254740996.0, 1];
        yield 'number 2, divisor 1' => [2, 1];
        yield 'number 400, divisor 1' => [400, 1];
        yield 'number 400.01, divisor 0.01' => [400.01, 0.01];
        yield 'number 2, divisor 10' => [2, 10, false];
        yield 'number 2.1, divisor 1' => [2.1, 1, false];
        yield 'number 400.0001, divisor 3' => [400.0001, 3, false];
        yield 'number 4000.00000000000001, divisor 0.00000000000001' => [4000.00000000000001, 0.00000000000001, true];
        yield 'number 4000.000000000000000000000000001, divisor 0.000000000000000000000000001' => [4000.000000000000000000000000001, 0.000000000000000000000000001, true];
    }

    public function multipleOfDataOnlyWorkingWithBCMath()
    {
        yield 'number 900719925474099166666.0, divisor 1' => [900719925474099166666.0, 1];
        yield 'number 4000.0001, divisor 0.0001, scale 4' => [400.0001, 0.0001, true, 4];
    }
}
