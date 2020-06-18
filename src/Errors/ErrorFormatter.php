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

namespace Opis\JsonSchema\Errors;

use Opis\JsonSchema\JsonPointer;

class ErrorFormatter
{
    /** @var callable[] */
    protected array $formatModes;

    /**
     * ErrorFormatter constructor.
     * @param callable[] $modes
     */
    public function __construct(array $modes = [])
    {
        // TODO: add default modes
        $this->formatModes = $modes;
    }

    /**
     * @param string $name
     * @param callable|null $formatter
     * @return ErrorFormatter
     */
    public function setFormatMode(string $name, ?callable $formatter): self
    {
        $this->formatModes[$name] = $formatter;
        return $this;
    }

    /**
     * @param string $name
     * @return callable|null
     */
    public function getFormatMode(string $name): ?callable
    {
        return $this->formatModes[$name] ?? null;
    }

    /**
     * @param ValidationError|null $error
     * @param string|callable $mode
     * @return array
     */
    public function format(?ValidationError $error, $mode = "flag"): array {
        if ($error === null) {
            return ['valid' => true];
        }

        if (!is_callable($mode)) {
            if (is_string($mode)) {
                $mode = $this->formatModes[$mode] ?? null;
            } else {
                $mode = null;
            }
        }

        if ($mode === null) {
            return ['valid' => false];
        }

        return $this->getNestedErrors($error, static function (ValidationError $error, ?array $subErrors = null) use ($mode) {
            $info = $mode($error);

            if (!is_array($info)) {
                $info = [];
            }

            $info['valid'] = false;
            if ($subErrors) {
                $info['errors'] = $subErrors;
            }

            return $info;
        });
    }

    /**
     * @param ValidationError $error
     * @param callable $formatter
     * @return mixed
     */
    public function formatNested(ValidationError $error, callable $formatter)
    {
        return $this->getNestedErrors($error, $formatter);
    }

    /**
     * @param ValidationError $error
     * @param callable $formatter
     * @return array
     */
    public function formatFlat(ValidationError $error, callable $formatter): array
    {
        $list = [];

        foreach ($this->getFlatErrors($error) as $error) {
            $list[] = $formatter($error);
        }

        return $list;
    }

    /**
     * @param ValidationError $error
     * @param callable $formatter
     * @param callable|null $key_formatter
     * @return array
     */
    public function formatKeyed(ValidationError $error, callable $formatter, ?callable $key_formatter = null): array
    {
        if (!$key_formatter) {
            $key_formatter = JsonPointer::class . '::pathToString';
        }

        $list = [];

        foreach ($this->getLeafErrors($error) as $error) {
            $key = $key_formatter($error->data()->fullPath());

            if (!isset($list[$key])) {
                $list[$key] = [];
            }

            $list[$key][] = $formatter($error);
        }

        return $list;
    }

    /**
     * @param ValidationError $error
     * @param callable $formatter
     * @return mixed
     */
    protected function getNestedErrors(ValidationError $error, callable $formatter)
    {
        if ($subErrors = $error->subErrors()) {
            foreach ($subErrors as &$subError) {
                $subError = $this->getNestedErrors($subError, $formatter);
                unset($subError);
            }
        }

        return $formatter($error, $subErrors);
    }

    /**
     * @param ValidationError $error
     * @return iterable|ValidationError[]
     */
    protected function getFlatErrors(ValidationError $error): iterable
    {
        yield $error;

        foreach ($error->subErrors() as $subError) {
            yield from $this->getFlatErrors($subError);
        }
    }

    /**
     * @param ValidationError $error
     * @return iterable|ValidationError[]
     */
    protected function getLeafErrors(ValidationError $error): iterable
    {
        if ($subErrors = $error->subErrors()) {
            foreach ($subErrors as $subError) {
                yield from $this->getLeafErrors($subError);
            }
        } else {
            yield $error;
        }
    }
}