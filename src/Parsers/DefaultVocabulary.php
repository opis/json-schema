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
    FiltersKeywordParser,
    SlotsKeywordParser
};
use Opis\JsonSchema\Parsers\Pragmas\{CastPragmaParser, GlobalsPragmaParser,
    MaxErrorsPragmaParser, SlotsPragmaParser};
use Opis\JsonSchema\Parsers\KeywordValidators\PragmaKeywordValidatorParser;

class DefaultVocabulary extends Vocabulary
{
    /**
     * @param KeywordParser[] $keywords
     * @param KeywordValidatorParser[] $keywordValidators
     * @param PragmaParser[] $pragmas
     */
    public function __construct(array $keywords = [], array $keywordValidators = [], array $pragmas = [])
    {
        $keywords = array_merge($keywords, [
            new FiltersKeywordParser('$filters'),
            new SlotsKeywordParser('$slots'),
        ]);

        $keywordValidators = array_merge([
            // $pragma has priority
            new PragmaKeywordValidatorParser('$pragma'),
        ], $keywordValidators);

        $pragmas = array_merge($pragmas, [
            new MaxErrorsPragmaParser('maxErrors'),
            new SlotsPragmaParser('slots'),
            new GlobalsPragmaParser('globals'),
            new CastPragmaParser('cast'),
        ]);

        parent::__construct($keywords, $keywordValidators, $pragmas);
    }
}