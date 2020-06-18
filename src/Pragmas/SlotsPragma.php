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

namespace Opis\JsonSchema\Pragmas;

use Opis\JsonSchema\{ValidationContext, Pragma};

class SlotsPragma implements Pragma
{

    protected array $slots;

    /**
     * @param array $slots
     */
    public function __construct(array $slots)
    {
        $this->slots = $slots;
    }

    /**
     * @inheritDoc
     */
    public function enter(ValidationContext $context)
    {
        $data = $context->slots();
        $context->setSlots($data ? $this->slots + $data : $this->slots);
        return $data;
    }

    /**
     * @inheritDoc
     */
    public function leave(ValidationContext $context, $data): void
    {
        $context->setSlots($data);
    }
}