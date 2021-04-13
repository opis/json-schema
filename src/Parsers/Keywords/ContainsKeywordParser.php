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
use Opis\JsonSchema\Keywords\ContainsKeyword;
use Opis\JsonSchema\Parsers\{KeywordParser, SchemaParser};

class ContainsKeywordParser extends KeywordParser
{
    protected ?string $minContains = null;
    protected ?string $maxContains = null;

    public function __construct(string $keyword, ?string $minContains = null, ?string $maxContains = null)
    {
        parent::__construct($keyword);
        $this->minContains = $minContains;
        $this->maxContains = $maxContains;
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

        if (!is_bool($value) && !is_object($value)) {
            throw $this->keywordException("{keyword} must be a json schema (object or boolean)", $info);
        }

        $min = $max = null;

        if ($this->minContains && $this->keywordExists($schema, $this->minContains)) {
            $min = $this->keywordValue($schema, $this->minContains);
            if (!is_int($min) || $min < 0) {
                throw $this->keywordException("{keyword} must be a non-negative integer", $info, $this->minContains);
            }
        }

        if ($this->maxContains && $this->keywordExists($schema, $this->maxContains)) {
            $max = $this->keywordValue($schema, $this->maxContains);
            if (!is_int($max) || $max < 0) {
                throw $this->keywordException("{keyword} must be a non-negative integer", $info, $this->maxContains);
            }
            if ($min !== null && $max < $min) {
                throw $this->keywordException("{keyword} must be greater than {$this->minContains}", $info, $this->maxContains);
            }
        } elseif ($min === 0) {
            return null;
        }

        return new ContainsKeyword($value, $min, $max);
    }
}