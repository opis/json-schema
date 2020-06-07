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

class Vocabulary implements IVocabulary
{
    /** @var IKeywordParser[] */
    protected array $keywords;

    /** @var IWrapperKeywordParser[] */
    protected array $wrappers;

    /** @var IPragmaParser[] */
    protected array $pragmas;

    /**
     * @param IKeywordParser[] $keywords
     * @param IWrapperKeywordParser[] $wrappers
     * @param IPragmaParser[] $pragmas
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
     * @param IKeywordParser $keyword
     * @return Vocabulary
     */
    public function appendKeyword(IKeywordParser $keyword): self
    {
        $this->keywords[] = $keyword;
        return $this;
    }

    /**
     * @param IKeywordParser $keyword
     * @return Vocabulary
     */
    public function prependKeyword(IKeywordParser $keyword): self
    {
        array_unshift($this->keywords, $keyword);
        return $this;
    }

    /**
     * @param IWrapperKeywordParser $wrapper
     * @return Vocabulary
     */
    public function appendWrapper(IWrapperKeywordParser $wrapper): self
    {
        $this->wrappers[] = $wrapper;
        return $this;
    }

    /**
     * @param IWrapperKeywordParser $wrapper
     * @return Vocabulary
     */
    public function prependWrapper(IWrapperKeywordParser $wrapper): self
    {
        array_unshift($this->wrappers, $wrapper);
        return $this;
    }

    /**
     * @param IPragmaParser $pragma
     * @return Vocabulary
     */
    public function appendPragma(IPragmaParser $pragma): self
    {
        $this->pragmas[] = $pragma;
        return $this;
    }

    /**
     * @param IPragmaParser $pragma
     * @return Vocabulary
     */
    public function prependPragma(IPragmaParser $pragma): self
    {
        array_unshift($this->pragmas, $pragma);
        return $this;
    }
}