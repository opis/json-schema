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

use ArrayObject;
use Opis\JsonSchema\ValidationContext;

trait OfTrait
{
    protected function createArrayObject(ValidationContext $context): ?ArrayObject
    {
        return $context->trackUnevaluated() ? new ArrayObject() : null;
    }

    protected function addEvaluatedFromArrayObject(?ArrayObject $object, ValidationContext $context): void
    {
        if (!$object || !$object->count()) {
            return;
        }

        foreach ($object as $value) {
            if (isset($value['properties'])) {
                $context->addEvaluatedProperties($value['properties']);
            }
            if (isset($value['items'])) {
                $context->addEvaluatedItems($value['items']);
            }
        }
    }
}