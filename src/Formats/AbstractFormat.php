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

use Opis\JsonSchema\IFormat;

abstract class AbstractFormat implements IFormat
{

    /**
     * @param $data
     * @param string $regex
     * @return bool
     */
    protected function validateRegex($data, string $regex): bool
    {
        return (bool) preg_match($regex, $data);
    }

    /**
     * @param $data
     * @param int $filter
     * @param null $options
     * @return bool
     */
    protected function validateFilter($data, int $filter, $options = 0): bool
    {
        return filter_var($data, $filter, $options) !== false;
    }

}