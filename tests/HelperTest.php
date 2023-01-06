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
     * @dataProvider multipleOfDataOnlyWorkingWithBCMath
     * @dataProvider multipleOfData
     */
    public function testIsMultipleOf($x, $y, bool $expectedTrue = true, int $scale = 14)
    {
        if ($expectedTrue) {
            $this->assertTrue(Helper::isMultipleOf($x, $y, $scale), sprintf('%s wasn\'t detected as a multiple of %s but it should', $x, $y));
            return;
        }

        $this->assertFalse(Helper::isMultipleOf($x, $y, $scale), sprintf('%s was detected as a multiple of %s but it shouldn\'t', $x, $y));
    }

    /**
     * @dataProvider multipleOfData
     */
    public function testIsMultipleOfWithoutBCMath($x, $y, bool $expectedTrue = true, int $scale = 14)
    {
        if ($expectedTrue) {
            $this->assertTrue(Helper::isMultipleOf($x, $y, $scale, false), sprintf('%s wasn\'t detected as a multiple of %s but it should', $x, $y));
            return;
        }

        $this->assertFalse(Helper::isMultipleOf($x, $y, $scale, false), sprintf('%s was detected as a multiple of %s but it shouldn\'t', $x, $y));
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
        yield 'number 4000.00000000000001, divisor 0.00000000000001, scale 14' => [4000.00000000000001, 0.00000000000001, false];
        yield 'number 4000.000000000000000000000000001, divisor 0.000000000000000000000000001, scale 14' => [4000.000000000000000000000000001, 0.000000000000000000000000001, false];

        // Scale should not be under 14 so it's not normal test case.
        yield 'number 2.2, divisor 1.15, scale 1' => [2.2, 1.15, true, 1];
        yield 'number 4000.0001, divisor 0.0001, scale 3' => [400.0001, 0.0001, false, 3];
        yield 'number 4000.0001, divisor 0.0001, scale 1' => [400.0001, 0.0001, false, 1];
        yield 'number 4000.0001, divisor 3, scale 2' => [400.0001, 3, false, 2];
    }

    public function multipleOfDataOnlyWorkingWithBCMath()
    {
        yield 'number 900719925474099166666.0, divisor 1' => [900719925474099166666.0, 1];
        yield 'number 4000.0001, divisor 0.0001, scale 4' => [400.0001, 0.0001, true, 4];
    }
}
