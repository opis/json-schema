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
use Opis\JsonSchema\Keywords\ItemsKeyword;
use Opis\JsonSchema\Parsers\{KeywordParser, SchemaParser};

class ItemsKeywordParser extends KeywordParser
{
    const ONLY_SCHEMA = 1;
    const ONLY_ARRAY = 2;
    const BOTH = 3;

    protected int $mode;
    protected ?string $startIndexKeyword;

    public function __construct(string $keyword, int $mode = self::BOTH, ?string $startIndexKeyword = null)
    {
        parent::__construct($keyword);
        $this->mode = $mode;
        $this->startIndexKeyword = $startIndexKeyword;
    }

    /**
     * @inheritDoc
     */
    public function type(): string
    {
        return self::TYPE_ARRAY;
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

        $value = $this->keywordValue($schema);

        $alwaysValid = false;

        if (is_bool($value)) {
            if ($this->mode === self::ONLY_ARRAY) {
                throw $this->keywordException("{keyword} must contain an array of json schemas", $info);
            }
            if ($value) {
                $alwaysValid = true;
            }
        } elseif (is_array($value)) {
            if ($this->mode === self::ONLY_SCHEMA) {
                throw $this->keywordException("{keyword} must contain a valid json schema", $info);
            }
            $valid = 0;
            foreach ($value as $index => $v) {
                if (is_bool($v)) {
                    if ($v) {
                        $valid++;
                    }
                } elseif (!is_object($v)) {
                    throw $this->keywordException("{keyword}[$index] must contain a valid json schema", $info);
                } elseif (!count(get_object_vars($v))) {
                    $valid++;
                }
            }
            if ($valid === count($value)) {
                $alwaysValid = true;
            }
        } elseif (!is_object($value)) {
            if ($this->mode === self::BOTH) {
                throw $this->keywordException("{keyword} must be a json schema or an array of json schemas", $info);
            } elseif ($this->mode === self::ONLY_ARRAY) {
                throw $this->keywordException("{keyword} must contain an array of json schemas", $info);
            } else {
                throw $this->keywordException("{keyword} must contain a valid json schema", $info);
            }
        } else {
            if ($this->mode === self::ONLY_ARRAY) {
                throw $this->keywordException("{keyword} must contain an array of json schemas", $info);
            }
            if (!count(get_object_vars($value))) {
                $alwaysValid = true;
            }
        }

        $startIndex = 0;
        if ($this->startIndexKeyword !== null && $this->keywordExists($schema, $this->startIndexKeyword)) {
            $start = $this->keywordValue($schema, $this->startIndexKeyword);
            if (is_array($start)) {
                $startIndex = count($start);
            }
        }

        return new ItemsKeyword($value, $alwaysValid, $this->keyword, $startIndex);
    }
}