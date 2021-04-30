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

use Opis\JsonSchema\Keyword;
use Opis\JsonSchema\Info\SchemaInfo;
use Opis\JsonSchema\Keywords\DependenciesKeyword;
use Opis\JsonSchema\Parsers\{KeywordParser, SchemaParser};

class DependenciesKeywordParser extends KeywordParser
{
    /**
     * @inheritDoc
     */
    public function type(): string
    {
        return self::TYPE_OBJECT;
    }

    /**
     * @inheritDoc
     */
    public function parse(SchemaInfo $info, SchemaParser $parser, object $shared): ?Keyword
    {
        if (!$parser->option('keepDependenciesKeyword') && !in_array($info->draft(), ['06', '07'])) {
            return null;
        }

        $schema = $info->data();

        if (!$this->keywordExists($schema)) {
            return null;
        }

        $value = $this->keywordValue($schema);
        if (!is_object($value)) {
            throw $this->keywordException("{keyword} must be an object", $info);
        }

        $list = get_object_vars($value);

        foreach ($list as $name => $s) {
            if (is_array($s)) {
                if (!$s) {
                    unset($list[$name]);
                    continue;
                }
                foreach ($s as $p) {
                    if (!is_string($p)) {
                        throw $this->keywordException("{keyword} must be an object containing json schemas or arrays of property names", $info);
                    }
                }
                $list[$name] = array_unique($s);
            } elseif (is_bool($s)) {
                if ($s) {
                    unset($list[$name]);
                }
            } elseif (!is_object($s)) {
                throw $this->keywordException("{keyword} must be an object containing json schemas or arrays of property names", $info);
            }
        }

        return $list ? new DependenciesKeyword($list) : null;
    }
}