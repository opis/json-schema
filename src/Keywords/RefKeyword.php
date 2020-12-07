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

use Opis\JsonSchema\{ValidationContext, Keyword, Schema};
use Opis\JsonSchema\Errors\ValidationError;

class RefKeyword implements Keyword
{
    use ErrorTrait;

    protected Schema $ref;
    protected bool $recursive;
    protected ?string $id = null;

    /**
     * @param Schema $ref
     * @param bool $recursive
     */
    public function __construct(Schema $ref, bool $recursive = false)
    {
        $this->ref = $ref;
        $this->recursive = $recursive;
    }

    /**
     * @inheritDoc
     */
    public function validate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        if ($error = $this->ref->validate($context)) {
            $kw = $this->recursive ? '$recursiveRef' : '$ref';
            return $this->error($schema, $context, $kw, 'The data must match ' . $kw, [], $error);
        }

        return null;
    }
}