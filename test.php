<?php

namespace Opis\JsonSchema;

use Opis\JsonSchema\Errors\ErrorFormatter;
use Opis\JsonSchema\Parsers\SchemaParser;
use Opis\JsonSchema\Resolvers\SchemaResolver;

require_once 'vendor/autoload.php';

$validator = new Validator(new SchemaLoader(new SchemaParser(), new SchemaResolver()));

$validator->resolver()->registerRaw(<<<'JSON'
{
            "$id": "http://localhost:4242/recursiveRef3/schema.json",
            "$recursiveAnchor": true,
            "$defs": {
                "myobject": {
                    "$id": "myobject.json",
                    "$recursiveAnchor": true,
                    "anyOf": [
                        { "type": "string" },
                        {
                            "type": "object",
                            "additionalProperties": { "$recursiveRef": "#" }
                        }
                    ]
                }
            },
            "anyOf": [
                { "type": "integer" },
                { "$ref": "#/$defs/myobject" }
            ]
        }
JSON
);


$data = <<<'JSON'
{ "foo": 1 }
JSON;

$data = json_decode($data);

$result = $validator->uriValidation($data, 'http://localhost:4242/recursiveRef3/schema.json');

print_r((new ErrorFormatter())->formatOutput($result, 'verbose'));

