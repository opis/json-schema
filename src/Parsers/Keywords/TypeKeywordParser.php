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

namespace Opis\JsonSchema\Parsers\Keywords;

use Opis\JsonSchema\{Helper, Keyword};
use Opis\JsonSchema\Info\SchemaInfo;
use Opis\JsonSchema\Keywords\TypeKeyword;
use Opis\JsonSchema\Parsers\{KeywordParser, SchemaParser};

class TypeKeywordParser extends KeywordParser
{
    /**
     * @inheritDoc
     */
    public function type(): string
    {
        return self::TYPE_BEFORE;
    }

    /**
     * @inheritDoc
     */
    public function parse(SchemaInfo $info, SchemaParser $parser, object $shared): ?Keyword
    {
        $schema = $info->data();

        if (!$this->keywordExists($schema)) {
            return null;
        }

        $type = $this->keywordValue($schema);

        if (is_string($type)) {
            $type = [$type];
        } elseif (!is_array($type)) {
            throw $this->keywordException('{keyword} can only be a string or an array of string', $info);
        }

        foreach ($type as $t) {
            if (!Helper::isValidJsonType($t)) {
                throw $this->keywordException("{keyword} contains invalid json type: {$t}", $info);
            }
        }

        $type = array_unique($type);

        if (!isset($shared->types)) {
            $shared->types = $type;
        } else {
            $shared->types = array_unique(array_merge($shared->types, $type));
        }

        $count = count($type);

        if ($count === 0) {
            throw $this->keywordException("{keyword} cannot be an empty array", $info);
        } elseif ($count === 1) {
            $type = reset($type);
        }

        return new TypeKeyword($type);
    }
}