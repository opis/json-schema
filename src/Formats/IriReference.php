<?php
/* ============================================================================
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

use Opis\JsonSchema\IFormat;

class IriReference implements IFormat
{
    /** @var bool */
    protected $hasIntl = false;

    /**
     * IriReference constructor.
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
            $data = parse_url($data);
            if (!$data) {
                return false;
            }
            foreach (['host', 'path', 'fragment'] as $component) {
                if (isset($data[$component])) {
                    $data[$component] = idn_to_ascii($data[$component], 0, INTL_IDNA_VARIANT_UTS46);
                }
            }
            $data = \Opis\JsonSchema\URI::build($data);
        }

        return \Opis\JsonSchema\URI::isValid($data, false);
    }
}