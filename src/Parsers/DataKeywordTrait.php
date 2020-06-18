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

namespace Opis\JsonSchema\Parsers;

use Opis\JsonSchema\JsonPointer;

trait DataKeywordTrait
{
    /**
     * @param $value
     * @return JsonPointer|null
     */
    protected function getDataKeywordPointer($value): ?JsonPointer
    {
        if (!is_object($value) || !property_exists($value, '$data') ||
            !is_string($value->{'$data'}) || count(get_object_vars($value)) !== 1) {
            return null;
        }

        return JsonPointer::parse($value->{'$data'});
    }

    /**
     * @param SchemaParser $parser
     * @param string|null $keyword
     * @return bool
     */
    protected function isDataKeywordAllowed(SchemaParser $parser, ?string $keyword = null): bool
    {
        if (!($enabled = $parser->option('allowDataKeyword'))) {
            return false;
        }

        if ($enabled === true) {
            return true;
        }

        if ($keyword === null) {
            return false;
        }

        return is_array($enabled) && in_array($keyword, $enabled);
    }
}