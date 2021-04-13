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

namespace Opis\JsonSchema\Parsers\Pragmas;

use Opis\JsonSchema\Pragma;
use Opis\JsonSchema\Pragmas\SlotsPragma;
use Opis\JsonSchema\Info\SchemaInfo;
use Opis\JsonSchema\Parsers\{PragmaParser, SchemaParser};

class SlotsPragmaParser extends PragmaParser
{
    /**
     * @inheritDoc
     */
    public function parse(SchemaInfo $info, SchemaParser $parser, object $shared): ?Pragma
    {
        if (!$parser->option('allowSlots') || !$this->pragmaExists($info)) {
            return null;
        }

        $value = $this->pragmaValue($info);

        if (!is_object($value)) {
            throw $this->pragmaException('Pragma {pragma} must be an object', $info);
        }

        $list = [];

        foreach ($value as $name => $slot) {
            if ($slot === null) {
                continue;
            }

            if (is_bool($slot)) {

                $list[$name] = $parser->parseSchema(new SchemaInfo(
                    $slot, null, $info->base(), $info->root(),
                    array_merge($info->path(), [$this->pragma, $name]),
                    $info->draft() ?? $parser->defaultDraftVersion()
                ));
            } elseif (is_string($slot) || is_object($slot)) {
                $list[$name] = $slot;
            } else {
                throw $this->pragmaException('Pragma {pragma} contains invalid value for slot ' . $name, $info);
            }
        }

        return $list ? new SlotsPragma($list) : null;
    }
}