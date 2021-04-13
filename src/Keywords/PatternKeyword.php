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

use Opis\JsonSchema\{Helper, ValidationContext, Keyword, Schema};
use Opis\JsonSchema\Errors\ValidationError;

class PatternKeyword implements Keyword
{
    use ErrorTrait;

    protected ?string $pattern;

    protected ?string $regex;

    /**
     * @param string $pattern
     */
    public function __construct(string $pattern)
    {
        $this->pattern = $pattern;
        $this->regex = Helper::patternToRegex($pattern);
    }

    /**
     * @inheritDoc
     */
    public function validate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        if (preg_match($this->regex, $context->currentData())) {
            return null;
        }

        return $this->error($schema, $context, 'pattern', "The string should match pattern: {pattern}", [
            'pattern' => $this->pattern,
        ]);
    }
}