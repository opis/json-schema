<?php
declare(strict_types=1);

namespace Opis\JsonSchema\Test;

use Opis\JsonSchema\IValidator;
use Opis\JsonSchema\ValidationResult;
use PHPUnit\Framework\TestCase;

class ValidationResultTest extends TestCase
{
    use JsonValidatorTrait;

    /** @var IValidator */
    protected $validator;

    public function getValidator(): IValidator
    {
        return $this->validator;
    }

    public function test__toString_withoutErrors_isValidJson()
    {
        $expected = '{"hasErrors": false, "errors": [], "maxErrors": 1, "totalErrors": 0}';
        $actual = (string)$this->uriValidation(10);

        $this->assertJsonStringEqualsJsonString($expected, $actual);
    }

    public function test__toString_withOneError_isValidJson()
    {
        $expected = <<<'JSON'
{
  "hasErrors": true,
  "errors": [
    {
      "data": "a",
      "dataPointer": [],
      "keywordArgs": {
        "expected": 10
      },
      "hasSubErrors": false,
      "subErrors": []
    }
  ],
  "maxErrors": 1,
  "totalErrors": 1
}
JSON;
        $actual = (string)$this->uriValidation('a');

        $this->assertJsonStringEqualsJsonString($expected, $actual);
    }

    protected function setUp()
    {
        parent::setUp();
        $this->validator = $this->createValidator();
    }

    /**
     * @param mixed $data
     *
     * @return ValidationResult
     */
    private function uriValidation($data): ValidationResult
    {
        return $this->getValidator()->uriValidation($data, 'schema:/basic.json#/definitions/constant');
    }
}
