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

namespace Opis\JsonSchema\Resolvers;

use Opis\JsonSchema\{Helper, Format, JsonPointer, Uri};
use Opis\JsonSchema\Formats\{
    DateTimeFormats, IriFormats, MiscFormats, UriFormats
};

class FormatResolver
{
    /** @var Format[][]|callable[][] */
    protected array $formats = [];

    /**
     * FormatResolver constructor.
     */
    public function __construct()
    {
        $this->formats = [
            'string' => [
                'date' => DateTimeFormats::class . '::date',
                'time' => DateTimeFormats::class . '::time',
                'date-time' => DateTimeFormats::class . '::dateTime',
                'duration' => DateTimeFormats::class . '::duration',

                'uri' => UriFormats::class . '::uri',
                'uri-reference' => UriFormats::class . '::uriReference',
                'uri-template' => UriFormats::class . '::uriTemplate',

                'regex' => Helper::class . '::isValidPattern',
                'ipv4' => MiscFormats::class . '::ipv4',
                'ipv6' => MiscFormats::class . '::ipv6',
                'uuid' => MiscFormats::class . '::uuid',

                'email' => MiscFormats::class . '::email',
                'hostname' => Uri::class . '::isValidHost',

                'json-pointer' => JsonPointer::class . '::isAbsolutePointer',
                'relative-json-pointer' => JsonPointer::class . '::isRelativePointer',

                'idn-hostname' => IriFormats::class . '::idnHostname',
                'idn-email' => IriFormats::class . '::idnEmail',
                'iri' => IriFormats::class . '::iri',
                'iri-reference' => IriFormats::class . '::iriReference',
            ],
        ];
    }

    /**
     * @param string $name
     * @param string $type
     * @return callable|Format|null
     */
    public function resolve(string $name, string $type)
    {
        return $this->formats[$type][$name] ?? null;
    }

    /**
     * @param string $name
     * @return Format[]|callable[]|null
     */
    public function resolveAll(string $name): ?array
    {
        $list = null;

        foreach ($this->formats as $type => $items) {
            if (isset($items[$name])) {
                $list[$type] = $items[$name];
            }
        }

        return $list;
    }

    /**
     * @param string $type
     * @param string $name
     * @param Format $format
     * @return FormatResolver
     */
    public function register(string $type, string $name, Format $format): self
    {
        $this->formats[$type][$name] = $format;

        return $this;
    }

    /**
     * @param string $type
     * @param string $name
     * @param callable $format
     * @return FormatResolver
     */
    public function registerCallable(string $type, string $name, callable $format): self
    {
        $this->formats[$type][$name] = $format;

        return $this;
    }

    /**
     * @param string $type
     * @param string $name
     * @return bool
     */
    public function unregister(string $type, string $name): bool
    {
        if (isset($this->formats[$type][$name])) {
            unset($this->formats[$type][$name]);

            return true;
        }

        return false;
    }

    public function __serialize(): array
    {
        return ['formats' => $this->formats];
    }

    public function __unserialize(array $data): void
    {
        $this->formats = $data['formats'];
    }
}