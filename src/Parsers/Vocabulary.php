<?php
/* ===========================================================================
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

abstract class Vocabulary
{
    /** @var KeywordParser[] */
    protected array $keywords;

    /** @var KeywordValidatorParser[] */
    protected array $keywordValidators;

    /** @var PragmaParser[] */
    protected array $pragmas;

    /**
     * @param KeywordParser[] $keywords
     * @param KeywordValidatorParser[] $keywordValidators
     * @param PragmaParser[] $pragmas
     */
    public function __construct(array $keywords = [], array $keywordValidators = [], array $pragmas = [])
    {
        $this->keywords = $keywords;
        $this->keywordValidators = $keywordValidators;
        $this->pragmas = $pragmas;
    }

    /**
     * @return KeywordParser[]
     */
    public function keywords(): array
    {
        return $this->keywords;
    }

    /**
     * @return KeywordValidatorParser[]
     */
    public function keywordValidators(): array
    {
        return $this->keywordValidators;
    }

    /**
     * @return PragmaParser[]
     */
    public function pragmas(): array
    {
        return $this->pragmas;
    }

    /**
     * @param KeywordParser $keyword
     * @return Vocabulary
     */
    public function appendKeyword(KeywordParser $keyword): self
    {
        $this->keywords[] = $keyword;
        return $this;
    }

    /**
     * @param KeywordParser $keyword
     * @return Vocabulary
     */
    public function prependKeyword(KeywordParser $keyword): self
    {
        array_unshift($this->keywords, $keyword);
        return $this;
    }

    /**
     * @param KeywordValidatorParser $keywordValidatorParser
     * @return Vocabulary
     */
    public function appendKeywordValidator(KeywordValidatorParser $keywordValidatorParser): self
    {
        $this->keywordValidators[] = $keywordValidatorParser;
        return $this;
    }

    /**
     * @param KeywordValidatorParser $keywordValidator
     * @return Vocabulary
     */
    public function prependKeywordValidator(KeywordValidatorParser $keywordValidator): self
    {
        array_unshift($this->keywordValidators, $keywordValidator);
        return $this;
    }

    /**
     * @param PragmaParser $pragma
     * @return Vocabulary
     */
    public function appendPragma(PragmaParser $pragma): self
    {
        $this->pragmas[] = $pragma;
        return $this;
    }

    /**
     * @param PragmaParser $pragma
     * @return Vocabulary
     */
    public function prependPragma(PragmaParser $pragma): self
    {
        array_unshift($this->pragmas, $pragma);
        return $this;
    }
}