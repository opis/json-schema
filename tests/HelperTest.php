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
}
