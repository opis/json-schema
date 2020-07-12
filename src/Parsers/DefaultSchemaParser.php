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

use Opis\JsonSchema\Resolvers\{
    DefaultFilterResolver,
    DefaultFormatResolver,
    DefaultContentMediaTypeResolver,
    DefaultContentEncodingResolver
};
use Opis\JsonSchema\Parsers\Drafts\{Draft06, Draft07};

class DefaultSchemaParser extends SchemaParser
{
    /**
     * @param array $resolvers
     * @param array $options
     * @param Vocabulary|null $extraVocabulary
     */
    public function __construct(
        array $resolvers = [],
        array $options = [],
        ?Vocabulary $extraVocabulary = null
    )
    {
        if (!array_key_exists('format', $resolvers)) {
            $resolvers['format'] = new DefaultFormatResolver();
        }

        if (!array_key_exists('contentEncoding', $resolvers)) {
            $resolvers['contentEncoding'] = new DefaultContentEncodingResolver();
        }

        if (!array_key_exists('contentMediaType', $resolvers)) {
            $resolvers['contentMediaType'] = new DefaultContentMediaTypeResolver();
        }

        if (!array_key_exists('$filters', $resolvers)) {
            $resolvers['$filters'] = new DefaultFilterResolver();
        }

        parent::__construct($this->getDrafts($extraVocabulary ?? new DefaultVocabulary()), $resolvers, $options);
    }

    /**
     * @param Vocabulary|null $extraVocabulary
     * @return array
     */
    protected function getDrafts(?Vocabulary $extraVocabulary): array
    {
        return [
            '06' => new Draft06($extraVocabulary),
            '07' => new Draft07($extraVocabulary),
        ];
    }
}