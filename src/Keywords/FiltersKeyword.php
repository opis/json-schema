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
    Filter,
    ValidationContext,
    Keyword,
    Schema
};
use Opis\JsonSchema\Errors\{ValidationError, CustomError};
use Opis\JsonSchema\Exceptions\UnresolvedFilterException;

class FiltersKeyword implements Keyword
{
    use ErrorTrait;

    /** @var array|object[] */
    protected array $filters;

    /**
     * @param object[] $filters
     */
    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    /**
     * @inheritDoc
     */
    public function validate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        $type = $context->currentDataType();

        foreach ($this->filters as $filter) {
            if (!isset($filter->types[$type])) {
                throw new UnresolvedFilterException($filter->name, $type, $schema, $context);
            }

            $func = $filter->types[$type];

            if ($filter->args) {
                $args = (array)$filter->args->resolve($context->rootData(), $context->currentDataPath());
                $args += $context->globals();
            } else {
                $args = $context->globals();
            }

            try {
                if ($func instanceof Filter) {
                    $ok = $func->validate($context, $schema, $args);
                } else {
                    $ok = $func($context->currentData(), $args);
                }
            } catch (CustomError $error) {
                return $this->error($schema, $context, '$filters', $error->getMessage(), $error->getArgs() + [
                    'filter' => $filter->name,
                    'type' => $type,
                    'args' => $args,
                ]);
            }

            if ($ok) {
                unset($func, $args, $ok);
                continue;
            }

            return $this->error($schema, $context, '$filters', "Filter '{filter}' ({type}) was not passed", [
                'filter' => $filter->name,
                'type' => $type,
                'args' => $args,
            ]);
        }

        return null;
    }
}