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

use Opis\JsonSchema\Parsers\Keywords\{
    ContentSchemaKeywordParser,
    FiltersKeywordParser,
    SlotsKeywordParser
};
use Opis\JsonSchema\Parsers\Pragmas\{CastPragmaParser, GlobalsPragmaParser,
    MaxErrorsPragmaParser, SlotsPragmaParser};
use Opis\JsonSchema\Parsers\WrapperKeywords\PragmaWrapperKeywordParser;

class DefaultVocabulary extends BaseVocabulary
{
    /**
     * @param KeywordParser[] $keywords
     * @param WrapperKeywordParser[] $wrappers
     * @param PragmaParser[] $pragmas
     */
    public function __construct(array $keywords = [], array $wrappers = [], array $pragmas = [])
    {
        $keywords = array_merge($keywords, [
            new ContentSchemaKeywordParser('contentSchema'),
            new FiltersKeywordParser('$filters'),
            new SlotsKeywordParser('$slots'),
        ]);

        $wrappers = array_merge([
            // $pragma has priority
            new PragmaWrapperKeywordParser('$pragma'),
        ], $wrappers);

        $pragmas = array_merge($pragmas, [
            new MaxErrorsPragmaParser('maxErrors'),
            new SlotsPragmaParser('slots'),
            new GlobalsPragmaParser('globals'),
            new CastPragmaParser('cast'),
        ]);

        parent::__construct($keywords, $wrappers, $pragmas);
    }
}