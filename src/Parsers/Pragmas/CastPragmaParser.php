<?php
/* ===========================================================================
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

namespace Opis\JsonSchema\Parsers\Pragmas;

use Opis\JsonSchema\Helper;
use Opis\JsonSchema\Pragma;
use Opis\JsonSchema\Info\SchemaInfo;
use Opis\JsonSchema\Pragmas\CastPragma;
use Opis\JsonSchema\Parsers\{PragmaParser, SchemaParser};

class CastPragmaParser extends PragmaParser
{
    /**
     * @inheritDoc
     */
    public function parse(SchemaInfo $info, SchemaParser $parser, object $shared): ?Pragma
    {
        if (!$this->pragmaExists($info)) {
            return null;
        }

        $value = $this->pragmaValue($info);

        if (!is_string($value) || !Helper::isValidJsonType($value)) {
            throw $this->pragmaException('Pragma {pragma} must contain a valid json type name', $info);
        }

        return new CastPragma($value);
    }
}