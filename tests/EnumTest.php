<?php

namespace Opis\JsonSchema\Test;

use Opis\JsonSchema\Helper;
use Opis\JsonSchema\Info\SchemaInfo;
use Opis\JsonSchema\Parsers\Keywords\EnumKeywordParser;
use Opis\JsonSchema\Parsers\SchemaParser;
use Opis\JsonSchema\Schema;
use Opis\JsonSchema\SchemaLoader;
use Opis\JsonSchema\Schemas\ObjectSchema;
use Opis\JsonSchema\ValidationContext;
use PHPUnit\Framework\TestCase;

class EnumTest extends TestCase
{
    /** @var SchemaParser */
    private $schemaParser;

    /** @var SchemaInfo */
    private $schemaInfo;

    /** @var ObjectSchema */
    private $schema;

    /** @var EnumKeywordParser */
    private $parser;

    /**
     * @before
     */
    public function before()
    {
        $this->schemaParser = new SchemaParser();
        $this->schemaInfo = $this->schemaInfo();
        $this->schema = new ObjectSchema($this->schemaInfo, null, null, null, null);
        $this->parser = new EnumKeywordParser('enum');
    }

    public function testEnumWithNullValue()
    {
        $sharedObject = new \stdClass();
        $sharedObject->types = ['string', 'null'];

        $keyword = $this->parser->parse($this->schemaInfo, $this->schemaParser, $sharedObject);
        $this->assertNotNull($keyword);

        $validationError = $keyword->validate($this->validationContext(null), $this->schema);
        $this->assertNull($validationError);
    }

    /**
     * @param mixed $data
     * @return ValidationContext
     */
    private function validationContext($data): ValidationContext
    {
        $loader = new SchemaLoader($this->schemaParser);

        return new ValidationContext($data, $loader);
    }

    private function schemaInfo(): SchemaInfo
    {
        $data = new \stdClass();
        $data->enum = ['bar', 'baz'];

        return new SchemaInfo($data, null);
    }
}
