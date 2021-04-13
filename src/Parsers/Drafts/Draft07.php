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

use Opis\JsonSchema\Parsers\Keywords\IfThenElseKeywordParser;

class Draft07 extends Draft06
{
    /**
     * @inheritDoc
     */
    public function version(): string
    {
        return '07';
    }

    /**
     * @inheritDoc
     */
    protected function getKeywordParsers(): array
    {
        $keywords = parent::getKeywordParsers();

        $keywords[] = new IfThenElseKeywordParser('if', 'then', 'else');

        return $keywords;
    }
}