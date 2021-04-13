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

use Opis\JsonSchema\{Keyword, Helper};
use Opis\JsonSchema\Info\SchemaInfo;
use Opis\JsonSchema\Keywords\DefaultKeyword;
use Opis\JsonSchema\Parsers\{KeywordParser, SchemaParser};

class DefaultKeywordParser extends KeywordParser
{

    protected ?string $properties = null;

    /**
     * @inheritDoc
     */
    public function __construct(string $keyword, ?string $properties = 'properties')
    {
        parent::__construct($keyword);
        $this->properties = $properties;
    }

    /**
     * @inheritDoc
     */
    public function type(): string
    {
        return self::TYPE_APPEND;
    }

    /**
     * @inheritDoc
     */
    public function parse(SchemaInfo $info, SchemaParser $parser, object $shared): ?Keyword
    {
        $schema = $info->data();

        if (!$parser->option('allowDefaults')) {
            return null;
        }

        $defaults = null;

        if ($this->keywordExists($schema)) {
            $defaults = $this->keywordValue($schema);

            if (is_object($defaults)) {
                $defaults = (array)Helper::cloneValue($defaults);
            } else {
                $defaults = null;
            }
        }

        if ($this->properties !== null && property_exists($schema, $this->properties)
            && is_object($schema->{$this->properties})) {
            foreach ($schema->{$this->properties} as $name => $value) {
                if (is_object($value) && property_exists($value, $this->keyword)) {
                    $defaults[$name] = $value->{$this->keyword};
                }
            }
        }

        if (!$defaults) {
            return null;
        }

        return new DefaultKeyword($defaults);
    }
}