<?php
declare(strict_types=1);

namespace Opis\JsonSchema\Test;

use Opis\JsonSchema\IValidator;
use Opis\JsonSchema\ValidationResult;
use PHPUnit\Framework\TestCase;

class ValidationErrorTest extends TestCase
{
    use JsonValidatorTrait;

    /** @var IValidator */
    protected $validator;

    public function getValidator(): IValidator
    {
        return $this->validator;
    }

    public function test_hasSubErrors_withNoSubErrors_isFalse()
    {
        $actual = $this->uriValidation('a')->getFirstError()->hasSubErrors();

        $this->assertFalse($actual);
    }

    public function test__toString_withOneError_isValidJson()
    {
        $expected = '{"data": "a", "dataPointer": [], "keywordArgs": {"expected": 10}, "hasSubErrors": false, "subErrors": []}';
        $actual = (string)$this->uriValidation('a')->getFirstError();

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
