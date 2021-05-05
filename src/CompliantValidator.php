<?php
/* ============================================================================
 * Copyright 2021 Zindex Software
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

namespace Opis\JsonSchema;

class CompliantValidator extends Validator
{
    protected const COMPLIANT_OPTIONS = [
        'allowFilters' => false,
        'allowFormats' => true,
        'allowMappers' => false,
        'allowTemplates' => false,
        'allowGlobals' => false,
        'allowDefaults' => false,
        'allowSlots' => false,
        'allowKeywordValidators' => false,
        'allowPragmas' => false,
        'allowDataKeyword' => false,
        'allowKeywordsAlongsideRef' => false,
        'allowUnevaluated' => true,
        'allowRelativeJsonPointerInRef' => false,
        'allowExclusiveMinMaxAsBool' => false,
        'keepDependenciesKeyword' => false,
        'keepAdditionalItemsKeyword' => false,
    ];

    public function __construct(?SchemaLoader $loader = null, int $max_errors = 1)
    {
        parent::__construct($loader, $max_errors);

        // Set parser options
        $parser = $this->parser();
        foreach (static::COMPLIANT_OPTIONS as $name => $value) {
            $parser->setOption($name, $value);
        }
    }
}
