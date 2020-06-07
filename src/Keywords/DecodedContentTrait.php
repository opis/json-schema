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

use Opis\JsonSchema\IContext;

trait DecodedContentTrait
{
    /**
     * @param IContext $context
     * @return string
     */
    protected function getDecodedContent(IContext $context): string
    {
        $shared = $context->sharedObject();
        if ($shared && isset($shared->decodedContent)) {
            return $shared->decodedContent;
        }

        return $context->currentData();
    }

    /**
     * @param IContext $context
     * @param string $content
     * @return bool
     */
    protected function setDecodedContent(IContext $context, string $content): bool
    {
        $shared = $context->sharedObject();
        if ($shared) {
            $shared->decodedContent = $content;

            return true;
        }

        return false;
    }
}