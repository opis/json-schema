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

namespace Opis\JsonSchema\Filters;

use Opis\JsonSchema\{IContext, IFilter, ISchema};
use Opis\JsonSchema\Resolvers\IFormatResolver;

class FormatExistsFilter implements IFilter
{
    /**
     * @inheritDoc
     */
    public function validate(IContext $context, ISchema $schema, array $args = []): bool
    {
        $format = $args['format'] ?? $context->currentData();
        if (!is_string($format)) {
            return false;
        }

        $type = null;
        if (isset($args['type'])) {
            if (!is_string($args['type'])) {
                return false;
            }
            $type = $args['type'];
        }

        /** @var IFormatResolver $resolver */
        $resolver = $context->loader()->parser()->resolver('format', IFormatResolver::class);
        if (!$resolver) {
            return false;
        }

        if ($type === null) {
            return (bool)$resolver->resolveAll($format);
        }

        return (bool)$resolver->resolve($format, $type);
    }
}