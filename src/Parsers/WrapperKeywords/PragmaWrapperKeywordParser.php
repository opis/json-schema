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

namespace Opis\JsonSchema\Parsers\WrapperKeywords;

use Opis\JsonSchema\IWrapperKeyword;
use Opis\JsonSchema\Info\{SchemaInfo, ISchemaInfo};
use Opis\JsonSchema\WrapperKeywords\PragmaWrapperKeyword;
use Opis\JsonSchema\Parsers\{AbstractWrapperKeywordParser, ISchemaParser};

class PragmaWrapperKeywordParser extends AbstractWrapperKeywordParser
{
    /**
     * @inheritDoc
     */
    public function parse(ISchemaInfo $info, ISchemaParser $parser, object $shared): ?IWrapperKeyword
    {
        if (!$parser->option('allowPragmas') || !$this->keywordExists($info)) {
            return null;
        }

        $value = $this->keywordValue($info);

        if (!is_object($value)) {
            throw $this->keywordException('{keyword} must be an object', $info);
        }

        $list = [];

        $draft = $info->draft() ?? $parser->defaultDraftVersion();

        $pragmaInfo = new SchemaInfo($value, null, $info->id() ?? $info->base(), $info->root(),
            array_merge($info->path(), [$this->keyword]), $draft);

        foreach ($parser->draft($draft)->pragmas() as $pragma) {
            if ($handler = $pragma->parse($pragmaInfo, $parser, $shared)) {
                $list[] = $handler;
            }
        }

        return $list ? new PragmaWrapperKeyword($list) : null;
    }
}