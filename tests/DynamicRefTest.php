<?php
/* ============================================================================
 * Copyright 2020 Zindex Software
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
use PHPUnit\Framework\TestCase;

class DynamicRefTest extends TestCase
{
    /**
     * @dataProvider dynamicReferencesProvider
     */
    public function testDynamicReferences(string $schema, string $data, bool $valid): void
    {
        $validator = new Validator();
        $result = $validator->validate(json_decode($data), json_decode($schema));
        $this->assertSame($valid, $result->isValid());
    }

    public function dynamicReferencesProvider(): iterable
    {
        $nested = <<<'JSON'
{
    "$schema": "https://json-schema.org/draft/2020-12/schema",
    "additionalProperties": false,
    "type": "object",
    "properties": {"foo": {"$ref": "#/$defs/nested"}, "version": {"type": "integer"}},
    "$defs": {
        "nested": {
            "$dynamicAnchor": "nested",
            "type": "object",
            "properties": {"+bar": {"type": "string"}},
            "patternProperties": {"^[a-zA-Z_][a-zA-Z0-9_]*$": {"$dynamicRef": "#nested"}}
        }
    }
}
JSON;
        yield 'nested anchor' => [$nested, '{"version":1,"foo":{"ook":{"+bar":"eek"}}}', true];
        yield 'deep nested anchor' => [$nested, '{"foo":{"ook":{"deeper":{"+bar":"eek"}}}}', true];
        yield 'nested constraints still apply' => [$nested, '{"foo":{"ook":{"+bar":1}}}', false];
        yield 'root constraints still apply' => [$nested, '{"extra":1}', false];

        $anchor = <<<'JSON'
{
    "$schema": "https://json-schema.org/draft/2020-12/schema",
    "$defs": {"target": {"$anchor":"static", "$dynamicAnchor":"dynamic", "type":"integer"}},
    "$dynamicRef":"#dynamic"
}
JSON;
        yield 'distinct static and dynamic anchors' => [$anchor, '1', true];
        yield 'dynamic target rejects strings' => [$anchor, '"one"', false];
        yield 'ordinary ref can use dynamic anchor' => [str_replace('"$dynamicRef"', '"$ref"', $anchor), '1', true];
        yield 'ordinary ref uses static anchor' => [str_replace('#dynamic', '#static', str_replace('"$dynamicRef"', '"$ref"', $anchor)), '1', true];
        yield 'same static and dynamic anchor' => [str_replace('"static"', '"dynamic"', $anchor), '1', true];

        $external = <<<'JSON'
{
    "$schema": "https://json-schema.org/draft/2020-12/schema",
    "$id":"https://example.com/root",
    "$defs": {
        "target": {"$id":"child", "$defs":{"nested":{"$dynamicAnchor":"node", "type":"integer"}}}
    },
    "$dynamicRef":"child#node"
}
JSON;
        yield 'URI reference to nested anchor in embedded resource' => [$external, '1', true];
        yield 'URI reference applies constraints' => [$external, '"one"', false];
        yield 'dynamic ref with pointer is static' => [str_replace('child#node', '#/$defs/target/$defs/nested', $external), '1', true];

        yield 'boolean pointer target' => ['{"$schema":"https://json-schema.org/draft/2020-12/schema","$defs":{"yes":true},"$dynamicRef":"#/$defs/yes"}', 'null', true];
        yield 'false pointer target' => ['{"$schema":"https://json-schema.org/draft/2020-12/schema","$defs":{"no":false},"$dynamicRef":"#/$defs/no"}', 'null', false];

        $override = <<<'JSON'
{
    "$schema": "https://json-schema.org/draft/2020-12/schema",
    "$id":"https://example.com/strict",
    "$defs": {
        "strict": {"$dynamicAnchor":"node", "type":"integer"},
        "middle": {"$id":"middle", "$ref":"loose"},
        "loose": {
            "$id":"loose",
            "$defs":{"target":{"$dynamicAnchor":"node", "type":"number"}},
            "$dynamicRef":"#node"
        }
    },
    "$ref":"middle"
}
JSON;
        yield 'outer resource anchor found outside root' => [$override, '1', true];
        yield 'outer scope crosses resource without matching anchor' => [$override, '1.5', false];
        yield 'outer constraints reject string' => [$override, '"one"', false];
        $static = str_replace('"$dynamicAnchor":"node", "type":"number"', '"$anchor":"node", "type":"number"', $override);
        yield 'static target does not use outer dynamic anchor' => [$static, '1.5', true];
    }
}
