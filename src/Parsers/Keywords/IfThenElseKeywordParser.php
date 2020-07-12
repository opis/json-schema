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
use Opis\JsonSchema\Keywords\IfThenElseKeyword;
use Opis\JsonSchema\Parsers\{KeywordParser, SchemaParser};

class IfThenElseKeywordParser extends KeywordParser
{

    protected string $then;

    protected string $else;

    /**
     * @param string $if
     * @param string $then
     * @param string $else
     */
    public function __construct(string $if, string $then, string $else)
    {
        parent::__construct($if);
        $this->then = $then;
        $this->else = $else;
    }

    /**
     * @inheritDoc
     */
    public function type(): string
    {
        return self::TYPE_AFTER;
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

        $if = $this->keywordValue($schema);
        if (!$this->isJsonSchema($if)) {
            throw $this->keywordException("{keyword} keyword must be a json schema", $info);
        }

        $then = true;
        if (property_exists($schema, $this->then)) {
            $then = $schema->{$this->then};
        }
        if (!$this->isJsonSchema($then)) {
            throw $this->keywordException("{keyword} keyword must be a json schema", $info, $this->then);
        }

        $else = true;
        if (property_exists($schema, $this->else)) {
            $else = $schema->{$this->else};
        }
        if (!$this->isJsonSchema($else)) {
            throw $this->keywordException("{keyword} keyword must be a json schema", $info, $this->else);
        }

        if ($if === true) {
            if ($then === true) {
                return null;
            }
            $else = true;
        } elseif ($if === false) {
            if ($else === true) {
                return null;
            }
            $then = true;
        } elseif ($then === true && $else === true) {
            return null;
        }

        return new IfThenElseKeyword($if, $then, $else);
    }

    /**
     * @param $value
     * @return bool
     */
    protected function isJsonSchema($value): bool
    {
        return is_bool($value) || is_object($value);
    }
}