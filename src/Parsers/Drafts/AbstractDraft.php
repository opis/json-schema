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

namespace Opis\JsonSchema\Parsers\Drafts;

use Opis\JsonSchema\Parsers\{
    Draft, PragmaParser, Vocabulary, KeywordParser, KeywordValidatorParser
};

abstract class AbstractDraft implements Draft
{
    /** @var KeywordParser[] */
    protected array $keywords;

    /** @var KeywordValidatorParser[] */
    protected array $keywordValidators;

    /** @var PragmaParser[] */
    protected array $pragmas;

    /**
     * @param Vocabulary|null $extraVocabulary
     */
    public function __construct(?Vocabulary $extraVocabulary = null)
    {
        $keywords = $this->getKeywordParsers();
        $keywordValidators = $this->getKeywordValidatorParsers();
        $pragmas = $this->getPragmaParsers();

        if ($extraVocabulary) {
            $keywords = array_merge($keywords, $extraVocabulary->keywords());
            $keywordValidators = array_merge($keywordValidators, $extraVocabulary->keywordValidators());
            $pragmas = array_merge($pragmas, $extraVocabulary->pragmas());
        }

        $keywords[] = $this->getRefKeywordParser();

        $this->keywords = $keywords;
        $this->keywordValidators = $keywordValidators;
        $this->pragmas = $pragmas;
    }

    /**
     * @inheritDoc
     */
    public function allowKeywordsAlongsideRef(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function keywords(): array
    {
        return $this->keywords;
    }

    /**
     * @inheritDoc
     */
    public function keywordValidators(): array
    {
        return $this->keywordValidators;
    }

    /**
     * @inheritDoc
     */
    public function pragmas(): array
    {
        return $this->pragmas;
    }

    /**
     * @return KeywordParser
     */
    abstract protected function getRefKeywordParser(): KeywordParser;

    /**
     * @return KeywordParser[]
     */
    abstract protected function getKeywordParsers(): array;

    /**
     * @return KeywordValidatorParser[]
     */
    protected function getKeywordValidatorParsers(): array
    {
        return [];
    }

    /**
     * @return PragmaParser[]
     */
    protected function getPragmaParsers(): array
    {
        return [];
    }
}