<?php

namespace Opis\JsonSchema\Test;

use Opis\JsonSchema\Formats\DateTimeFormats;
use Opis\JsonSchema\Formats\IriFormats;
use Opis\JsonSchema\Formats\MiscFormats;
use Opis\JsonSchema\JsonPointer;
use Opis\JsonSchema\Parsers\SchemaParser;
use Opis\JsonSchema\Validator;
use PHPUnit\Framework\TestCase;

/**
 * PCRE's `$` also matches before a trailing newline. Every value here is valid as given and must be refused with a
 * newline appended.
 */
class TrailingNewlineTest extends TestCase
{
    public function formatsProvider(): array
    {
        return [
            ['date', '2016-01-01'],
            ['date', '2016-02-29'],
            ['date', '1963-06-19'],
            ['time', '10:00:00Z'],
            ['time', '10:00:00z'],
            ['time', '23:59:59.123456Z'],
            ['time', '08:30:06+01:00'],
            ['time', '08:30:06-05:30'],
            ['time', '10:00:00'],
            ['date-time', '2016-01-01T10:00:00Z'],
            ['date-time', '2016-01-01t10:00:00z'],
            ['date-time', '1963-06-19T08:30:06.283185Z'],
            ['date-time', '1963-06-19T08:30:06+01:00'],
            ['date-time', '2016-01-01T10:00:00'],
            ['duration', 'P1D'],
            ['duration', 'P4Y'],
            ['duration', 'P1Y2M3D'],
            ['duration', 'PT1H'],
            ['duration', 'PT30M'],
            ['duration', 'PT0S'],
            ['duration', 'P1DT12H'],
            ['duration', 'P2W'],
            ['uuid', '123e4567-e89b-12d3-a456-426614174000'],
            ['uuid', '123E4567-E89B-12D3-A456-426614174000'],
            ['uuid', '00000000-0000-0000-0000-000000000000'],
        ];
    }

    /**
     * @dataProvider formatsProvider
     */
    public function testFormatRefusesTrailingNewline(string $format, string $value)
    {
        $validator = new Validator();
        $schema = (object)['type' => 'string', 'format' => $format];

        $this->assertTrue($validator->validate($value, $schema)->isValid());
        $this->assertFalse($validator->validate($value . "\n", $schema)->isValid());
    }

    public function testFormatFunctionsRefuseTrailingNewline()
    {
        $this->assertTrue(DateTimeFormats::date('2016-01-01'));
        $this->assertFalse(DateTimeFormats::date("2016-01-01\n"));

        $this->assertTrue(DateTimeFormats::time('10:00:00Z'));
        $this->assertFalse(DateTimeFormats::time("10:00:00Z\n"));

        $this->assertTrue(DateTimeFormats::dateTime('2016-01-01T10:00:00Z'));
        $this->assertFalse(DateTimeFormats::dateTime("2016-01-01T10:00:00Z\n"));

        $this->assertTrue(DateTimeFormats::duration('P1D'));
        $this->assertFalse(DateTimeFormats::duration("P1D\n"));

        $this->assertTrue(MiscFormats::uuid('123e4567-e89b-12d3-a456-426614174000'));
        $this->assertFalse(MiscFormats::uuid("123e4567-e89b-12d3-a456-426614174000\n"));
    }

    public function idnEmailProvider(): array
    {
        return [
            ['name@example.com'],
            ['first.last@example.org'],
            ['a+tag@sub.example.co'],
        ];
    }

    /**
     * @dataProvider idnEmailProvider
     */
    public function testIdnEmailRefusesTrailingNewline(string $value)
    {
        // The name and domain are matched before the IDN conversion, so any conversion reaches the regex.
        $idn = static fn(string $part): string => $part;

        $this->assertTrue(IriFormats::idnEmail($value, $idn));
        $this->assertFalse(IriFormats::idnEmail($value . "\n", $idn));
    }

    /**
     * A reference token may hold a newline, so only pointers ending in the level, the shift or `#` apply.
     */
    public function relativePointerProvider(): array
    {
        return [
            ['0'],
            ['1'],
            ['1#'],
            ['2/foo/bar#'],
            ['0+1'],
            ['1-2#'],
        ];
    }

    /**
     * @dataProvider relativePointerProvider
     */
    public function testRelativePointerRefusesTrailingNewline(string $pointer)
    {
        $this->assertTrue(JsonPointer::isRelativePointer($pointer));
        $this->assertFalse(JsonPointer::isRelativePointer($pointer . "\n"));
    }

    public function testRelativePointerFormatRefusesTrailingNewline()
    {
        $validator = new Validator();
        $schema = (object)['type' => 'string', 'format' => 'relative-json-pointer'];

        $this->assertTrue($validator->validate('1#', $schema)->isValid());
        $this->assertFalse($validator->validate("1#\n", $schema)->isValid());
    }

    public function testParseRefusesTrailingNewlineAfterFragment()
    {
        $this->assertNotNull(JsonPointer::parse('1#'));
        $this->assertNull(JsonPointer::parse("1#\n"));

        $this->assertNotNull(JsonPointer::parse('2/foo#'));
        $this->assertNull(JsonPointer::parse("2/foo#\n"));

        $this->assertNotNull(JsonPointer::parse('1-2#'));
        $this->assertNull(JsonPointer::parse("1-2#\n"));
    }

    public function testShiftRefusesIndexWithTrailingNewline()
    {
        $pointer = JsonPointer::parse('0+1');

        $this->assertSame(['a', 6], $pointer->absolutePath(['a', '5']));
        $this->assertNull($pointer->absolutePath(['a', "5\n"]));
    }

    public function draftProvider(): array
    {
        return [
            ['https://json-schema.org/draft/2020-12/schema', '2020-12'],
            ['https://json-schema.org/draft/2020-12/schema#', '2020-12'],
            ['https://json-schema.org/draft/2019-09/schema', '2019-09'],
            ['http://json-schema.org/draft-07/schema#', '07'],
            ['http://json-schema.org/draft-06/schema#', '06'],
        ];
    }

    /**
     * @dataProvider draftProvider
     */
    public function testDraftRefusesTrailingNewline(string $uri, string $draft)
    {
        $parser = new SchemaParser();

        $this->assertSame($draft, $parser->parseDraftVersion($uri));
        $this->assertNull($parser->parseDraftVersion($uri . "\n"));
    }
}
