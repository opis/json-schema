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

use Opis\JsonSchema\{Helper, ValidationContext, Pragma};

class CastPragma implements Pragma
{

    protected string $cast;

    /** @var callable */
    protected $func;

    /**
     * @param string $cast
     */
    public function __construct(string $cast)
    {
        $this->cast = $cast;
        $this->func = $this->getCastFunction($cast);
    }

    /**
     * @inheritDoc
     */
    public function enter(ValidationContext $context)
    {
        $currentType = $context->currentDataType();
        if ($currentType !== null && Helper::jsonTypeMatches($currentType, $this->cast)) {
            // Cast not needed
            return $this;
        }
        unset($currentType);

        $currentData = $context->currentData();

        $context->setCurrentData(($this->func)($currentData));

        return $currentData;
    }

    /**
     * @inheritDoc
     */
    public function leave(ValidationContext $context, $data): void
    {
        if ($data !== $this) {
            $context->setCurrentData($data);
        }
    }

    /**
     * @param string $type
     * @return callable
     */
    protected function getCastFunction(string $type): callable
    {
        $f = 'toNull';

        switch ($type) {
            case 'integer':
                $f = 'toInteger';
                break;
            case 'number':
                $f = 'toNumber';
                break;
            case 'string':
                $f = 'toString';
                break;
            case 'array':
                $f = 'toArray';
                break;
            case 'object':
                $f = 'toObject';
                break;
            case 'boolean':
                $f = 'toBoolean';
                break;
        }

        return [$this, $f];
    }

    /**
     * @param $value
     * @return int|null
     */
    public function toInteger($value): ?int
    {
        if ($value === null) {
            return 0;
        }

        return is_scalar($value) ? intval($value) : null;
    }

    /**
     * @param $value
     * @return float|null
     */
    public function toNumber($value): ?float
    {
        if ($value === null) {
            return 0.0;
        }

        return is_scalar($value) ? floatval($value) : null;
    }

    /**
     * @param $value
     * @return string|null
     */
    public function toString($value): ?string
    {
        if ($value === null) {
            return '';
        }

        if (is_scalar($value)) {
            return (string) $value;
        }

        return null;
    }

    /**
     * @param $value
     * @return array|null
     */
    public function toArray($value): ?array
    {
        if ($value === null) {
            return [];
        }

        if (is_scalar($value)) {
            return [$value];
        }

        if (is_array($value)) {
            return array_values($value);
        }

        if (is_object($value)) {
            return array_values(get_object_vars($value));
        }

        return null;
    }

    /**
     * @param $value
     * @return object|null
     */
    public function toObject($value): ?object
    {
        if (is_object($value) || is_array($value)) {
            return (object) $value;
        }

        return null;
    }

    /**
     * @param $value
     * @return bool
     */
    public function toBoolean($value): bool
    {
        if ($value === null) {
            return false;
        }
        if (is_string($value)) {
            return !($value === '');
        }
        if (is_object($value)) {
            return count(get_object_vars($value)) > 0;
        }
        return boolval($value);
    }
}