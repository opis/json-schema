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

namespace Opis\JsonSchema;

class URI
{

    const EMPTY_COMPONENTS = [
        'scheme' => null,
        'user' => null,
        'pass' => null,
        'host' => null,
        'port' => null,
        'path' => null,
        'query' => null,
        'fragment' => null
    ];

    const FRAGMENT_REGEX = '/^(?:(%[0-9a-f]{2})|[a-z0-9\-\/?:@._~!\$&\'\(\)*+,;=])*$/i';

    const PATH_REGEX = '/^(?:(%[0-9a-f]{2})|[a-z0-9\/:@\-._~\!\$&\'\(\)*+,;=])*$/i';

    const HOSTNAME_REGEX = '/^(([a-z0-9]|[a-z0-9][a-z0-9\-]*[a-z0-9]){1,63}\.)*([a-z0-9]|[a-z0-9][a-z0-9\-]*[a-z0-9]){1,63}$/i';

    const TEMPLATE_VARSPEC_REGEX = '~^(?<varname>[a-zA-Z0-9\_\%\.]+)(?:(?<explode>\*)?|\:(?<prefix>\d+))?$~';

    const TEMPLATE_REGEX = <<<'REGEX'
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

    const TEMPLATE_TABLE = [
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

    /**
     * @param string $uri
     * @param bool $require_scheme
     * @return bool
     */
    public static function isValid(string $uri, bool $require_scheme = true): bool
    {
        if ($uri === '#') {
            return !$require_scheme;
        }
        $uri = parse_url($uri);
        if (!$uri) {
            return false;
        }
        if ($require_scheme && (!isset($uri['scheme']) || $uri['scheme'] === '')) {
            return false;
        }
        if (isset($uri['host']) && !static::isValidHostname($uri['host'])) {
            return false;
        }
        if (isset($uri['path']) && !static::isValidPath($uri['path'])) {
            return false;
        }
        if (isset($uri['fragment']) && !static::isValidFragment($uri['fragment'])) {
            return false;
        }
        return true;
    }

    /**
     * @param string $host
     * @return bool
     */
    public static function isValidHostname(string $host): bool
    {
        if (preg_match( static::HOSTNAME_REGEX, $host)) {
            return true;
        }
        if (preg_match('/^\[(?<ip>[^\]]+)\]$/', $host, $m)) {
            $host = $m['ip'];
        }
        return (bool) filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
    }

    /**
     * @param string $path
     * @return bool
     */
    public static function isValidPath(string $path): bool
    {
        return (bool) preg_match(static::PATH_REGEX, $path);
    }

    /**
     * @param string $fragment
     * @return bool
     */
    public static function isValidFragment(string $fragment): bool
    {
        return (bool) preg_match(static::FRAGMENT_REGEX, $fragment);
    }

    /**
     * @param string $uri
     * @return array
     */
    public static function parse(string $uri): array
    {
        if ($uri === '') {
            return static::EMPTY_COMPONENTS;
        } elseif ($uri[0] === '#') {
            return ['fragment' => substr($uri, 1)] + static::EMPTY_COMPONENTS;
        } elseif ($uri[0] === '?') {
            $uri = substr($uri, 1);
            if (($pos = strpos($uri, '#')) !== false) {
                return ['query' => substr($uri, 0, $pos), 'fragment' => substr($uri, $pos + 1)] + static::EMPTY_COMPONENTS;
            }
            return ['query' => $uri] + static::EMPTY_COMPONENTS;
        }
        return parse_url($uri) + static::EMPTY_COMPONENTS;
    }

    /**
     * @param array $components
     * @return string
     */
    public static function build(array $components): string
    {
        if (empty($components)) {
            return '';
        }

        $uri = $components['path'] ?? '/';

        if (isset($components['query'])) {
            $uri .= '?' . $components['query'];
        }

        if (isset($components['fragment'])) {
            $uri .= '#' . $components['fragment'];
        }

        if (isset($components['host'])) {
            $authority = $components['host'];

            if (isset($components['port'])) {
                $authority .= ':' . $components['port'];
            }

            if (isset($components['user'])) {
                $authority = $components['user'] . '@' . $authority;
            }

            if ($uri !== '') {
                if ($uri[0] !== '/' && $uri[0] !== '?' && $uri[0] !== '#') {
                    $uri = '/' . $uri;
                }
            }

            $uri = '//' . $authority . $uri;
        }

        if (isset($components['scheme'])) {
            if ('file' === $components['scheme']) {
                $uri = '//' . $uri;
            }
            return $components['scheme'] . ':' . $uri;
        }

        return $uri;
    }

    /**
     * @param string $uri
     * @param string $base
     * @param bool $force_fragment
     * @return string
     */
    public static function merge(string $uri, string $base, bool $force_fragment = true): string
    {
        if ($uri === '') {
            if ($force_fragment && strpos($base, '#') === false) {
                $base .= '#';
            }
            return $base;
        }
        if ($uri[0] === '#') {
            if (($pos = strpos($base, '#')) !== false) {
                return substr($base, 0, $pos) . $uri;
            }

            return $base . $uri;
        } elseif ($uri[0] === '?') {
            if (($pos = strpos($base, '?')) !== false) {
                return substr($base, 0, $pos) . $uri;
            }
            if ($force_fragment && strpos($uri, '#') === false) {
                $uri .= '#';
            }
            return $base . $uri;
        }

        $uri = static::parse($uri);
        $base = static::parse($base);

        if (!isset($uri['scheme'])) {
            $uri['scheme'] = $base['scheme'];
        }

        if (!isset($uri['host'])) {
            $uri['host'] = $base['host'];
            $uri['port'] = $base['port'];
            $uri['user'] = $base['user'];
            $uri['pass'] = $base['pass'];
        }

        if (!isset($uri['path'])) {
            $uri['path'] = $base['path'];
            if (!isset($uri['query'])) {
                $uri['query'] = $base['query'];
            }
        } elseif (isset($base['path'])) {
            if (isset($uri['path'][0]) && $uri['path'][0] !== '/') {
                $path = explode('/', $base['path']);
                array_pop($path);
                $path = implode('/', $path);
                $uri['path'] = $path . '/' . $uri['path'];
                unset($path);
            }
        }

        if ($force_fragment && $uri['fragment'] === null) {
            $uri['fragment'] = '';
        }

        return static::build($uri);
    }

    /**
     * @param string $uri
     * @return string
     */
    public static function normalize(string $uri): string
    {
        return static::merge($uri, '', true);
    }

    /**
     * @param string $uri
     * @return bool
     */
    public static function isTemplate(string $uri): bool
    {
        return (bool)preg_match(static::TEMPLATE_REGEX, $uri);
    }

    /**
     * @param string $uri
     * @param object|array $vars
     * @return string
     */
    public static function parseTemplate(string $uri, $vars): string
    {
        if (!is_object($vars)) {
            $vars = (object)$vars;
        }
        return preg_replace_callback(static::TEMPLATE_REGEX, function (array $m) use ($vars) {
            $operator = $m['operator'] ?? '';
            if (!isset(static::TEMPLATE_TABLE[$operator])) {
                return $m[0];
            }
            $varlist = explode(',', $m['varlist']);
            unset($m);

            $data = [];
            // Check if operator is ok
            foreach ($varlist as $var) {
                if (!preg_match(static::TEMPLATE_VARSPEC_REGEX, $var, $spec)) {
                    continue;
                }

                $varname = rawurldecode($spec['varname']);
                if (!isset($vars->{$varname})) {
                    continue;
                }
                $data[$varname] = [
                    'name' => $spec['varname'],
                    'value' => is_scalar($vars->{$varname}) ? (string) $vars->{$varname} : $vars->{$varname},
                    'explode' => isset($spec['explode']) && $spec['explode'] === '*',
                    'prefix' => isset($spec['prefix']) ? (int) $spec['prefix'] : 0,
                ];
                unset($spec, $var);
            }

            if (empty($data)) {
                return '';
            }

            return static::parseTemplateExpression(static::TEMPLATE_TABLE[$operator], $data);
        }, $uri);
    }


    /**
     * @param array $table
     * @param array $data
     * @return string
     */
    protected static function parseTemplateExpression(array $table, array $data): string
    {
        $result = [];

        /** @var callable $substr */
        $substr = function_exists('mb_substr') ? 'mb_substr' : 'substr';

        foreach ($data as $var) {
            $str = "";
            if (is_string($var['value'])) {
                if ($table['named']) {
                    $str .= $var['name'];
                    if ($var['value'] === '') {
                        $str .= $table['ifemp'];
                    }
                    else {
                        $str .= '=';
                    }
                }
                if ($var['prefix']) {
                    $str .= static::encodeTemplateString($substr($var['value'], 0, $var['prefix']), $table['allow']);
                }
                else {
                    $str .= static::encodeTemplateString($var['value'], $table['allow']);
                }
            }
            elseif ($var['explode']) {
                $list = [];
                if ($table['named']) {
                    if (is_array($var['value'])) {
                        foreach ($var['value'] as $v) {
                            if (is_null($v)) {
                                continue;
                            }
                            $v = static::encodeTemplateString((string) $v, $table['allow']);
                            if ($v === '') {
                                $list[] = $var['name'] . $table['ifemp'];
                            }
                            else {
                                $list[] = $var['name'] . '=' . $v;
                            }
                        }
                    }
                    elseif (is_object($var['value'])) {
                        foreach ($var['value'] as $prop => $v) {
                            if (is_null($v)) {
                                continue;
                            }
                            $v = static::encodeTemplateString((string) $v, $table['allow']);
                            $prop = static::encodeTemplateString((string) $prop, $table['allow']);
                            if ($v === '') {
                                $list[] = $prop . $table['ifemp'];
                            }
                            else {
                                $list[] = $prop . '=' . $v;
                            }
                        }
                    }
                }
                else {
                    if (is_array($var['value'])) {
                        foreach ($var['value'] as $v) {
                            if (is_null($v)) {
                                continue;
                            }
                            $list[] = static::encodeTemplateString($v, $table['allow']);
                        }
                    }
                    elseif (is_object($var['value'])) {
                        foreach ($var['value'] as $prop => $v) {
                            if (is_null($v)) {
                                continue;
                            }
                            $v = static::encodeTemplateString((string) $v, $table['allow']);
                            $prop = static::encodeTemplateString((string) $prop, $table['allow']);
                            $list[] = $prop . '=' . $v;
                        }
                    }
                }

                if ($list) {
                    $str .= implode($table['sep'], $list);
                }
                unset($list);
            }
            else {
                if ($table['named']) {
                    $str .= $var['name'];
                    if ($var['value'] === '') {
                        $str .= $table['ifemp'];
                    }
                    else {
                        $str .= '=';
                    }
                }
                $list = [];
                if (is_array($var['value'])) {
                    foreach ($var['value'] as $v) {
                        $list[] = static::encodeTemplateString($v, $table['allow']);
                    }
                }
                elseif (is_object($var['value'])) {
                    /** @noinspection PhpWrongForeachArgumentTypeInspection */
                    foreach ($var['value'] as $prop => $v) {
                        $list[] = static::encodeTemplateString((string) $prop, $table['allow']);
                        $list[] = static::encodeTemplateString((string) $v, $table['allow']);
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
    public static function encodeTemplateString(string $data, bool $reserved): string
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

}
