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
use Opis\JsonSchema\Parsers\{KeywordParser, DataKeywordTrait,
    SchemaParser};
use Opis\JsonSchema\Keywords\{RequiredDataKeyword, RequiredKeyword};

class RequiredKeywordParser extends KeywordParser
{
    use DataKeywordTrait;

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
        $schema = $info->data();

        if (!$this->keywordExists($schema)) {
            return null;
        }

        $value = $this->keywordValue($schema);

        $filter = $this->propertiesFilter($parser, $schema);

        if ($this->isDataKeywordAllowed($parser, $this->keyword)) {
            if ($pointer = $this->getDataKeywordPointer($value)) {
                return new RequiredDataKeyword($pointer, $filter);
            }
        }

        if (!is_array($value)) {
            throw $this->keywordException("{keyword} must be an array of strings", $info);
        }

        foreach ($value as $name) {
            if (!is_string($name)) {
                throw $this->keywordException("{keyword} must be an array of strings", $info);
            }
        }

        if ($filter) {
            $value = array_filter($value, $filter);
        }

        return $value ? new RequiredKeyword(array_unique($value)) : null;
    }

    /**
     * @param SchemaParser $parser
     * @param object $schema
     * @return callable|null
     */
    protected function propertiesFilter(SchemaParser $parser, object $schema): ?callable
    {
        if (!$parser->option('allowDefaults')) {
            return null;
        }

        if (!property_exists($schema, 'properties') || !is_object($schema->properties)) {
            return null;
        }

        $props = $schema->properties;

        return static function (string $name) use ($props) {
            if (!property_exists($props, $name)) {
                return true;
            }

            if (is_object($props->{$name}) && property_exists($props->{$name}, 'default')) {
                return false;
            }

            return true;
        };
    }
}