<?php
/* ===========================================================================
 * Copyright 2018 Zindex Software
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

namespace Opis\JsonSchema\Formats;

class IdnHostname extends AbstractFormat
{

    /** @var bool */
    protected $hasIntl = false;

    /**
     * IdnEmail constructor.
     */
    public function __construct()
    {
        $this->hasIntl = function_exists('idn_to_ascii');
    }

    /**
     * @inheritDoc
     */
    public function validate($data): bool
    {
        if ($this->hasIntl) {
            $data = idn_to_ascii($data, IDNA_CHECK_CONTEXTJ | IDNA_USE_STD3_RULES | IDNA_CHECK_BIDI, INTL_IDNA_VARIANT_UTS46);
            if ($data === false) {
                return false;
            }
        }
        return $this->validateRegex($data, \Opis\JsonSchema\URI::HOSTNAME_REGEX);
    }

}