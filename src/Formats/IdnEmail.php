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

class IdnEmail extends AbstractFormat
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
            if (!preg_match('/^(?<name>.+)@(?<domain>.+)$/u', $data, $m)) {
                return false;
            }
            $m['name'] = idn_to_ascii($m['name'], 0, INTL_IDNA_VARIANT_UTS46);
            $m['domain'] = idn_to_ascii($m['domain'], 0, INTL_IDNA_VARIANT_UTS46);
            $data = $m['name'] . '@' . $m['domain'];
        }
        return $this->validateFilter($data, FILTER_VALIDATE_EMAIL);
    }
}