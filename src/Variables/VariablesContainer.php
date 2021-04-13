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

namespace Opis\JsonSchema\Variables;

use Opis\JsonSchema\JsonPointer;
use Opis\JsonSchema\Variables;

final class VariablesContainer implements Variables
{

    /**
     * @var array|object|Variables|null
     */
    private $vars;

    private bool $parsed = false;

    private bool $hasRefs = false;

    /** @var string[] */
    private array $keys;

    /**
     * @param array|object $data
     * @param bool $lazy
     * @param string $ref_key
     * @param string $each_key
     * @param string $default_key
     */
    public function __construct($data, bool $lazy = true, string $ref_key = '$ref', string $each_key = '$each', string $default_key = 'default')
    {
        $this->keys = [
            'ref' => $ref_key,
            'each' => $each_key,
            'default' => $default_key,
        ];

        if ($lazy) {
            $this->vars = $data;
        } else {
            $this->parsed = true;
            $this->vars = $this->parse($data);
        }
    }

    /**
     * @inheritdoc
     */
    public function resolve($data, array $path = [])
    {
        if (!$this->parsed) {
            $this->vars = $this->parse($this->vars);
            $this->parsed = true;
        }

        if (!$this->hasRefs) {
            // Nothing to resolve
            return $this->vars;
        }

        return $this->deepClone($this->vars, $data, $path);
    }

    /**
     * @param $vars
     * @param $data
     * @param string[]|int[] $path
     * @return array|object|mixed
     */
    private function deepClone($vars, $data, array $path)
    {
        $toObject = false;
        if (is_object($vars)) {
            if ($vars instanceof Variables) {
                return $vars->resolve($data, $path);
            }
            $vars = get_object_vars($vars);
            $toObject = true;
        } elseif (!is_array($vars)) {
            return $vars;
        }

        foreach ($vars as &$var) {
            if ($var !== null && !is_scalar($var)) {
                $var = $this->deepClone($var, $data, $path);
            }
            unset($var);
        }

        return $toObject ? (object)$vars : $vars;
    }

    /**
     * @param mixed $data
     * @return mixed
     */
    private function parse($data)
    {
        if (is_array($data)) {
            return array_map([$this, 'parse'], $data);
        }

        if (!is_object($data)) {
            return $data;
        }

        if ($vars = $this->parseRef($data)) {
            $this->hasRefs = true;

            return $vars;
        }

        return (object)array_map([$this, 'parse'], get_object_vars($data));
    }

    /**
     * @param object $data
     * @return null|Variables
     */
    private function parseRef(object $data): ?Variables
    {
        if (!property_exists($data, $this->keys['ref'])) {
            return null;
        }

        $ref = $data->{$this->keys['ref']};
        if (!is_string($ref)) {
            return null;
        }

        $pointer = JsonPointer::parse($ref);
        if ($pointer === null) {
            return null;
        }

        $each = null;
        if (property_exists($data, $this->keys['each']) && is_object($data->{$this->keys['each']})) {
            $each = new self($data->{$this->keys['each']}, !$this->parsed, $this->keys['ref'], $this->keys['each'], $this->keys['default']);
        }

        if (property_exists($data, $this->keys['default'])) {
            return new RefVariablesContainer($pointer, $each, $data->{$this->keys['default']});
        }

        return new RefVariablesContainer($pointer, $each);
    }
}