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

namespace Opis\JsonSchema\Keywords;

use Opis\JsonSchema\{
    Helper,
    IContext,
    IKeyword,
    ISchema
};
use Opis\JsonSchema\Errors\IValidationError;

class UniqueItemsKeyword implements IKeyword
{
    use ErrorTrait;

    /**
     * @inheritDoc
     */
    public function validate(IContext $context, ISchema $schema): ?IValidationError
    {
        $data = $context->currentData();
        if (!$data) {
            return null;
        }

        $count = count($data);

        for ($i = 0; $i < $count - 1; $i++) {
            for ($j = $i + 1; $j < $count; $j++) {
                if (Helper::equals($data[$i], $data[$j])) {
                    return $this->error($schema, $context, 'uniqueItems', 'Array must have unique items', [
                        'duplicate' => $data[$i],
                        'indexes' => [$i, $j],
                    ]);
                }
            }
        }

        return null;
    }
}