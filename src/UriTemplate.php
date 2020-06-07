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

namespace Opis\JsonSchema;

use Opis\String\UnicodeString;

class UriTemplate
{
    /** @var string */
    protected const TEMPLATE_VARSPEC_REGEX = '~^(?<varname>[a-zA-Z0-9\_\%\.]+)(?:(?<explode>\*)?|\:(?<prefix>\d+))?$~';

    /** @var string */
    protected const TEMPLATE_REGEX = <<<'REGEX'
~\{
(?<operator>[+#./;&=,!@|\?])?
(?<varlist>
  (?:(?P>varspec),)*
  (?<varspec>(?:
    [a-zA-Z0-9\_\%\.]+
    (?:\*|\:\d+)?
  ))
)
\}~x
REGEX;

    /** @var array */
    protected const TEMPLATE_TABLE = [
        '' => [
            'first' => '',
            'sep' => ',',
            'named' => false,
            'ifemp' => '',
            'allow' => false,
        ],
        '+' => [
            'first' => '',
            'sep' => ',',
            'named' => false,
            'ifemp' => '',
            'allow' => true,
        ],
        '.' => [
            'first' => '.',
            'sep' => '.',
            'named' => false,
            'ifemp' => '',
            'allow' => false,
        ],
        '/' => [
            'first' => '/',
            'sep' => '/',
            'named' => false,
            'ifemp' => '',
            'allow' => false,
        ],
        ';' => [
            'first' => ';',
            'sep' => ';',
            'named' => true,
            'ifemp' => '',
            'allow' => false,
        ],
        '?' => [
            'first' => '?',
            'sep' => '&',
            'named' => true,
            'ifemp' => '=',
            'allow' => false,
        ],
        '&' => [
            'first' => '&',
            'sep' => '&',
            'named' => true,
            'ifemp' => '=',
            'allow' => false,
        ],
        '#' => [
            'first' => '#',
            'sep' => ',',
            'named' => false,
            'ifemp' => '',
            'allow' => true,
        ],
    ];

    protected string $uri;

    /** @var bool|null|array */
    protected $parsed = false;

    /**
     * UriTemplate constructor.
     * @param string $uri_template
     */
    public function __construct(string $uri_template)
    {
        $this->uri = $uri_template;
    }

    /**
     * @param array $vars
     * @param callable|null $subString
     * @return string
     */
    public function resolve(array $vars, callable $subString = null): string
    {
        if ($this->parsed === false) {
            $this->parsed = $this->parse($this->uri);
        }
        if ($this->parsed === null || !$vars) {
            return $this->uri;
        }

        $data = '';

        foreach ($this->parsed as $item) {
            if (!is_array($item)) {
                $data .= $item;
                continue;
            }

            $resolved = [];
            foreach ($item['vars'] as $name => $info) {
                if (!isset($vars[$name])) {
                    continue;
                }
                $resolved[$name] = is_scalar($vars[$name])
                    ? (string)$vars[$name] : $vars[$name];
            }

            $data .= $this->parseTemplateExpression(
                self::TEMPLATE_TABLE[$item['operator']],
                $this->resolveVars($item['vars'], $vars),
                $subString
            );
        }

        return $data;
    }

    /**
     * @return bool
     */
    public function hasPlaceholders(): bool
    {
        if ($this->parsed === false) {
            $this->parse($this->uri);
        }

        return $this->parsed !== null;
    }

    /**
     * @param string $uri
     * @return array|null
     */
    protected function parse(string $uri): ?array
    {
        $placeholders = null;
        preg_match_all(self::TEMPLATE_REGEX, $uri, $placeholders, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
        if (!$placeholders) {
            return null;
        }

        $dataIndex = -1;
        $data = [];

        $hasVars = false;
        $nextOffset = 0;
        foreach ($placeholders as &$p) {
            $offset = $p[0][1];
            if ($nextOffset < $offset) {
                $data[] = substr($uri, $nextOffset, $offset - $nextOffset);
                $dataIndex++;
            }
            $matched = $p[0][0];
            $nextOffset = $offset + strlen($matched);

            $operator = $p['operator'][0] ?? null;
            if ($operator === null || !isset(self::TEMPLATE_TABLE[$operator])) {
                if ($dataIndex >= 0 && is_string($data[$dataIndex])) {
                    $data[$dataIndex] .= $matched;
                } else {
                    $data[] = $matched;
                    $dataIndex++;
                }
                continue;
            }

            $varList = $p['varlist'][0] ?? '';
            $varList = $varList === '' ? [] : explode(',', $varList);
            $p = null;

            $varData = [];
            foreach ($varList as $var) {
                if (!preg_match(self::TEMPLATE_VARSPEC_REGEX, $var, $spec)) {
                    continue;
                }

                $varName = rawurldecode($spec['varname']);

                $varData[$varName] = [
                    'name' => $spec['varname'],
                    'explode' => isset($spec['explode']) && $spec['explode'] === '*',
                    'prefix' => isset($spec['prefix']) ? (int)$spec['prefix']
                        : 0,
                ];

                unset($var, $spec, $varName);
            }

            if ($varData) {
                $hasVars = true;
                $data[] = [
                    'operator' => $operator,
                    'vars' => $varData,
                ];
                $dataIndex++;
            } else {
                if ($dataIndex >= 0 && is_string($data[$dataIndex])) {
                    $data[$dataIndex] .= $matched;
                } else {
                    $data[] = $matched;
                    $dataIndex++;
                }
            }

            unset($varData, $varList, $operator);
        }

        if (!$hasVars) {
            return null;
        }

        $matched = substr($uri, $nextOffset);
        if ($matched !== false && $matched !== '') {
            if ($dataIndex >= 0 && is_string($data[$dataIndex])) {
                $data[$dataIndex] .= $matched;
            } else {
                $data[] = $matched;
            }
        }

        return $data;
    }

    /**
     * @param array $vars
     * @param array $data
     * @return array
     */
    protected function resolveVars(array $vars, array $data): array
    {
        $resolved = [];
        foreach ($vars as $name => $info) {
            if (!isset($data[$name])) {
                continue;
            }
            $resolved[$name] = $info + [
                    'value' => is_scalar($data[$name]) ? (string)$data[$name]
                        : $data[$name],
                ];
        }

        return $resolved;
    }

    /**
     * @param array $table
     * @param array $data
     * @param callable|null $subStr
     * @return string
     */
    protected function parseTemplateExpression(array $table, array $data, callable $subStr = null): string
    {
        $result = [];
        foreach ($data as $var) {
            $str = "";
            if (is_string($var['value'])) {
                if ($table['named']) {
                    $str .= $var['name'];
                    if ($var['value'] === '') {
                        $str .= $table['ifemp'];
                    } else {
                        $str .= '=';
                    }
                }
                if ($var['prefix']) {
                    if ($subStr === null) {
                        $str .= $this->encodeTemplateString($this->subString($var['value'], 0, $var['prefix']),
                            $table['allow']);
                    } else {
                        $str .= $this->encodeTemplateString((string)$subStr($var['value'], 0, $var['prefix']),
                            $table['allow']);
                    }
                } else {
                    $str .= $this->encodeTemplateString($var['value'], $table['allow']);
                }
            } elseif ($var['explode']) {
                $list = [];
                if ($table['named']) {
                    if (is_array($var['value'])) {
                        foreach ($var['value'] as $v) {
                            if (is_null($v) || !is_scalar($v)) {
                                continue;
                            }
                            $v = $this->encodeTemplateString((string)$v, $table['allow']);
                            if ($v === '') {
                                $list[] = $var['name'] . $table['ifemp'];
                            } else {
                                $list[] = $var['name'] . '=' . $v;
                            }
                        }
                    } elseif (is_object($var['value'])) {
                        foreach ($var['value'] as $prop => $v) {
                            if (is_null($v) || !is_scalar($v)) {
                                continue;
                            }
                            $v = $this->encodeTemplateString((string)$v, $table['allow']);
                            $prop = $this->encodeTemplateString((string)$prop, $table['allow']);
                            if ($v === '') {
                                $list[] = $prop . $table['ifemp'];
                            } else {
                                $list[] = $prop . '=' . $v;
                            }
                        }
                    }
                } else {
                    if (is_array($var['value'])) {
                        foreach ($var['value'] as $v) {
                            if (is_null($v) || !is_scalar($v)) {
                                continue;
                            }
                            $list[] = $this->encodeTemplateString($v, $table['allow']);
                        }
                    } elseif (is_object($var['value'])) {
                        foreach ($var['value'] as $prop => $v) {
                            if (is_null($v) || !is_scalar($v)) {
                                continue;
                            }
                            $v = $this->encodeTemplateString((string)$v, $table['allow']);
                            $prop = $this->encodeTemplateString((string)$prop, $table['allow']);
                            $list[] = $prop . '=' . $v;
                        }
                    }
                }

                if ($list) {
                    $str .= implode($table['sep'], $list);
                }
                unset($list);
            } else {
                if ($table['named']) {
                    $str .= $var['name'];
                    if ($var['value'] === '') {
                        $str .= $table['ifemp'];
                    } else {
                        $str .= '=';
                    }
                }
                $list = [];
                if (is_array($var['value'])) {
                    foreach ($var['value'] as $v) {
                        $list[] = $this->encodeTemplateString($v, $table['allow']);
                    }
                } elseif (is_object($var['value'])) {
                    foreach ($var['value'] as $prop => $v) {
                        $list[] = $this->encodeTemplateString((string)$prop, $table['allow']);
                        $list[] = $this->encodeTemplateString((string)$v, $table['allow']);
                    }
                }
                if ($list) {
                    $str .= implode(',', $list);
                }
                unset($list);
            }

            if ($str !== '') {
                $result[] = $str;
            }
        }

        if (!$result) {
            return '';
        }

        $result = implode($table['sep'], $result);

        if ($result !== '') {
            $result = $table['first'] . $result;
        }

        return $result;
    }

    /**
     * @param string $data
     * @param bool $reserved
     * @return string
     */
    protected function encodeTemplateString(string $data, bool $reserved): string
    {
        $skip = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-._~';

        if ($reserved) {
            $skip .= ':/?#[]@!$&\'()*+,;=';
        }

        $result = '';
        $temp = '';
        for ($i = 0, $len = strlen($data); $i < $len; $i++) {
            if (strpos($skip, $data[$i]) !== false) {
                if ($temp !== '') {
                    $result .= rawurlencode($temp);
                    $temp = '';
                }
                $result .= $data[$i];
                continue;
            }
            if ($reserved && $data[$i] === '%') {
                if (isset($data[$i + 1]) && isset($data[$i + 2])
                    && strpos('ABCDEF0123456789', $data[$i + 1]) !== false
                    && strpos('ABCDEF0123456789', $data[$i + 2]) !== false) {
                    if ($temp !== '') {
                        $result .= rawurlencode($temp);
                    }
                    $result .= '%' . $data[$i + 1] . $data[$i + 2];
                    $i += 3;
                    continue;
                }
            }
            $temp .= $data[$i];
        }

        if ($temp !== '') {
            $result .= rawurlencode($temp);
        }

        return $result;
    }

    /**
     * @param string $data
     * @param int $offset
     * @param int|null $length
     * @return string
     */
    protected function subString(string $data, int $offset, int $length = null): string
    {
        return (string)UnicodeString::from($data)->substring($offset, $length);
    }

    /**
     * @return string
     */
    public function value(): string
    {
        return $this->uri;
    }

    public function __toString(): string
    {
        return $this->uri;
    }

    /**
     * @param string $uri
     * @return bool
     */
    public static function isTemplate(string $uri): bool
    {
        $open = substr_count($uri, '{');
        if ($open === 0) {
            return false;
        }
        $close = substr_count($uri, '}');
        if ($open !== $close) {
            return false;
        }

        return (bool)preg_match(self::TEMPLATE_REGEX, $uri);
    }
}