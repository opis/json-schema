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

class BaseVocabulary implements Vocabulary
{
    /** @var KeywordParser[] */
    protected array $keywords;

    /** @var WrapperKeywordParser[] */
    protected array $wrappers;

    /** @var PragmaParser[] */
    protected array $pragmas;

    /**
     * @param KeywordParser[] $keywords
     * @param WrapperKeywordParser[] $wrappers
     * @param PragmaParser[] $pragmas
     */
    public function __construct(array $keywords = [], array $wrappers = [], array $pragmas = [])
    {
        $this->keywords = $keywords;
        $this->wrappers = $wrappers;
        $this->pragmas = $pragmas;
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
    public function wrappers(): array
    {
        return $this->wrappers;
    }

    /**
     * @inheritDoc
     */
    public function pragmas(): array
    {
        return $this->pragmas;
    }

    /**
     * @param KeywordParser $keyword
     * @return BaseVocabulary
     */
    public function appendKeyword(KeywordParser $keyword): self
    {
        $this->keywords[] = $keyword;
        return $this;
    }

    /**
     * @param KeywordParser $keyword
     * @return BaseVocabulary
     */
    public function prependKeyword(KeywordParser $keyword): self
    {
        array_unshift($this->keywords, $keyword);
        return $this;
    }

    /**
     * @param WrapperKeywordParser $wrapper
     * @return BaseVocabulary
     */
    public function appendWrapper(WrapperKeywordParser $wrapper): self
    {
        $this->wrappers[] = $wrapper;
        return $this;
    }

    /**
     * @param WrapperKeywordParser $wrapper
     * @return BaseVocabulary
     */
    public function prependWrapper(WrapperKeywordParser $wrapper): self
    {
        array_unshift($this->wrappers, $wrapper);
        return $this;
    }

    /**
     * @param PragmaParser $pragma
     * @return BaseVocabulary
     */
    public function appendPragma(PragmaParser $pragma): self
    {
        $this->pragmas[] = $pragma;
        return $this;
    }

    /**
     * @param PragmaParser $pragma
     * @return BaseVocabulary
     */
    public function prependPragma(PragmaParser $pragma): self
    {
        array_unshift($this->pragmas, $pragma);
        return $this;
    }
}