<?php

namespace Opis\JsonSchema\Test;

function array_to_object(array $source): \stdClass {
    return json_decode(json_encode((object)$source));
}