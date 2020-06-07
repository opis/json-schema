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
    IDraft, IPragmaParser, IVocabulary, IKeywordParser, IWrapperKeywordParser
};

abstract class AbstractDraft implements IDraft
{
    /** @var IKeywordParser[] */
    protected array $keywords;

    /** @var IWrapperKeywordParser[] */
    protected array $wrappers;

    /** @var IPragmaParser[] */
    protected array $pragmas;

    /**
     * @param IVocabulary|null $extraVocabulary
     */
    public function __construct(?IVocabulary $extraVocabulary = null)
    {
        $keywords = $this->getKeywordParsers();
        $wrappers = $this->getWrapperKeywordParsers();
        $pragmas = $this->getPragmaParsers();

        if ($extraVocabulary) {
            $keywords = array_merge($keywords, $extraVocabulary->keywords());
            $wrappers = array_merge($wrappers, $extraVocabulary->wrappers());
            $pragmas = array_merge($pragmas, $extraVocabulary->pragmas());
        }

        $keywords[] = $this->getRefKeywordParser();

        $this->keywords = $keywords;
        $this->wrappers = $wrappers;
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
     * @return IKeywordParser
     */
    abstract protected function getRefKeywordParser(): IKeywordParser;

    /**
     * @return IKeywordParser[]
     */
    abstract protected function getKeywordParsers(): array;

    /**
     * @return IWrapperKeywordParser[]
     */
    protected function getWrapperKeywordParsers(): array
    {
        return [];
    }

    /**
     * @return IPragmaParser[]
     */
    protected function getPragmaParsers(): array
    {
        return [];
    }
}