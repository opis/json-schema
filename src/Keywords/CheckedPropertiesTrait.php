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

use Opis\JsonSchema\ValidationContext;

trait CheckedPropertiesTrait
{
    /**
     * @param ValidationContext $context
     * @param array $props
     * @return bool
     */
    protected function addCheckedProperties(ValidationContext $context, array $props): bool
    {
        $shared = $context->sharedObject();
        if (!$shared) {
            return false;
        }

        if (!isset($shared->checkedProperties)) {
            $shared->checkedProperties = $props;
        } else {
            $shared->checkedProperties = array_unique(array_merge($shared->checkedProperties, $props));
        }

        return true;
    }

    /**
     * @param ValidationContext $context
     * @return array|null
     */
    protected function getCheckedProperties(ValidationContext $context): ?array
    {
        $shared = $context->sharedObject();
        if (!$shared || !isset($shared->checkedProperties)) {
            return null;
        }

        return $shared->checkedProperties;
    }
}