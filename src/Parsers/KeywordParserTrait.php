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

use Opis\JsonSchema\Info\SchemaInfo;
use Opis\JsonSchema\Exceptions\InvalidKeywordException;

trait KeywordParserTrait
{
    /** @var string */
    protected string $keyword;

    /**
     * @param string $keyword
     */
    public function __construct(string $keyword)
    {
        $this->keyword = $keyword;
    }

    /**
     * @param object|SchemaInfo $schema
     * @param string|null $keyword
     * @return bool
     */
    protected function keywordExists(object $schema, ?string $keyword = null): bool
    {
        if ($schema instanceof SchemaInfo) {
            $schema = $schema->data();
        }

        return property_exists($schema, $keyword ?? $this->keyword);
    }

    /**
     * @param object|SchemaInfo $schema
     * @param string|null $keyword
     * @return mixed
     */
    protected function keywordValue(object $schema, ?string $keyword = null)
    {
        if ($schema instanceof SchemaInfo) {
            $schema = $schema->data();
        }

        return $schema->{$keyword ?? $this->keyword};
    }

    /**
     * @param string $message
     * @param SchemaInfo $info
     * @param string|null $keyword
     * @return InvalidKeywordException
     */
    protected function keywordException(string $message, SchemaInfo $info, ?string $keyword = null): InvalidKeywordException
    {
        $keyword = $keyword ?? $this->keyword;

        return new InvalidKeywordException(str_replace('{keyword}', $keyword, $message), $keyword, $info);
    }
}