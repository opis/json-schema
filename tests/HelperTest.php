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

    public function testIsMultipleOfFloatPrecision()
    {
        if (!extension_loaded('bcmath')) {
            $this->markTestSkipped('BCMath is required to reproduce the number_format precision bug');
        }

        $this->assertTrue(Helper::isMultipleOf(32.49, 0.01));
        $this->assertTrue(Helper::isMultipleOf(0.29, 0.01));
        $this->assertTrue(Helper::isMultipleOf(2222.22, 0.01));
        $this->assertTrue(Helper::isMultipleOf(123456789.01, 0.01));
        $this->assertTrue(Helper::isMultipleOf(2.0e-5, 1.0e-5));

        $this->assertFalse(Helper::isMultipleOf(32.495, 0.01));
        $this->assertFalse(Helper::isMultipleOf(32.49000000000001, 0.01));
        $this->assertFalse(Helper::isMultipleOf(0.1 + 0.2, 0.1));
    }
}
