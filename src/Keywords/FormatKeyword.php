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
    ValidationContext,
    Format,
    Keyword,
    Schema
};
use Opis\JsonSchema\Errors\{ValidationError, CustomError};

class FormatKeyword implements Keyword
{
    use ErrorTrait;

    protected ?string $name;

    /** @var callable[]|Format[] */
    protected ?array $types;

    /**
     * @param string $name
     * @param callable[]|Format[] $types
     */
    public function __construct(string $name, array $types)
    {
        $this->name = $name;
        $this->types = $types;
    }

    /**
     * @inheritDoc
     */
    public function validate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        $type = $context->currentDataType();

        if (!isset($this->types[$type])) {
            return null;
        }

        $format = $this->types[$type];

        try {
            if ($format instanceof Format) {
                $ok = $format->validate($context->currentData());
            } else {
                $ok = $format($context->currentData());
            }
        } catch (CustomError $error) {
            return $this->error($schema, $context, 'format', $error->getMessage(), $error->getArgs() + [
                'format' => $this->name,
                'type' => $type,
            ]);
        }

        if ($ok) {
            return null;
        }

        return $this->error($schema, $context, 'format', "The data must match the '{format}' format", [
            'format' => $this->name,
            'type' => $type,
        ]);
    }
}
