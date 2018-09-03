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

class FormatContainer implements IFormatContainer
{

    /** @var array */
    protected $formats = [
        'string' => [
            'date' => Formats\Date::class,
            'date-time' => Formats\DateTime::class,
            'email' => Formats\Email::class,
            'idn-email' => Formats\IdnEmail::class,
            'hostname' => Formats\Hostname::class,
            'idn-hostname' => Formats\IdnHostname::class,
            'ipv4' => Formats\IPv4::class,
            'ipv6' => Formats\IPv6::class,
            'json-pointer' => Formats\JsonPointer::class,
            'regex' => Formats\Regex::class,
            'relative-json-pointer' => Formats\RelativeJsonPointer::class,
            'time' => Formats\Time::class,
            'uri' => Formats\Uri::class,
            'uri-reference' => Formats\UriReference::class,
            'uri-template' => Formats\UriTemplate::class,
            'iri' => Formats\Iri::class,
            'iri-reference' => Formats\IriReference::class,
        ]
    ];

    /**
     * @inheritDoc
     */
    public function get(string $type, string $name)
    {
        if (!isset($this->formats[$type][$name])) {
            return null;
        }
        if (is_string($this->formats[$type][$name])) {
            $class = $this->formats[$type][$name];
            $this->formats[$type][$name] = new $class();
            if (!($this->formats[$type][$name] instanceof IFormat)) {
                unset($this->formats[$type][$name]);
                return null;
            }
        }
        return $this->formats[$type][$name];
    }

    /**
     * @param string $type
     * @param string $name
     * @param IFormat $format
     * @return FormatContainer
     */
    public function add(string $type, string $name, IFormat $format): self
    {
        $this->formats[$type][$name] = $format;
        return $this;
    }

}