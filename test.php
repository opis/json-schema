<?php

namespace Opis\JsonSchema;

use Opis\JsonSchema\Errors\ErrorFormatter;
use Opis\JsonSchema\Formats\IriFormats;
use Opis\JsonSchema\Parsers\SchemaParser;
use Opis\JsonSchema\Resolvers\SchemaResolver;

require_once 'vendor/autoload.php';

$validator = new Validator(new SchemaLoader(new SchemaParser(), new SchemaResolver()));

$validator->resolver()->registerPrefix('http://json-schema.org/', __DIR__ . '/tests/official/drafts/');
$validator->resolver()->registerPrefix('https://json-schema.org/', __DIR__ . '/tests/official/drafts/');

$validator->resolver()->registerRaw(<<<'JSON'
{
"$id": "http://example.com/x",
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
{ "$recursiveRefxx": true, "$ref": "#/$defs/myobject" }
]
}
JSON
);


$data = <<<'JSON'
{ "foo": 1 }
JSON;

$data = json_decode($data);

$validator->setMaxErrors(3);
$result = $validator->uriValidation($data, 'http://example.com/x');

print_r((new ErrorFormatter())->formatOutput($result, 'verbose'));

