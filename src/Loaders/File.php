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

namespace Opis\JsonSchema\Loaders;

use Opis\JsonSchema\Schema;

class File extends Memory
{

    /** @var string[] */
    protected $dirs;

    /** @var string */
    protected $prefix;

    /** @var int */
    protected $prefixLength;

    /**
     * File constructor.
     * @param string $prefix
     * @param string[] $dirs
     */
    public function __construct(string $prefix, array $dirs)
    {
        $this->dirs = $dirs;
        $this->prefix = $prefix;
        $this->prefixLength = strlen($prefix);
    }

    /**
     * @inheritDoc
     */
    public function loadSchema(string $uri)
    {
        if (isset($this->schemas[$uri])) {
            return $this->schemas[$uri];
        }
        if ($this->prefixLength !== 0 && strpos($uri, $this->prefix) !== 0) {
            return null;
        }
        $path = substr($uri, $this->prefixLength);

        $schema = null;
        foreach ($this->dirs as $dir) {
            if (file_exists($dir . $path)) {
                $schema = json_decode(file_get_contents($dir . $path), false);
                $schema = new Schema($schema, $uri);
                break;
            }
        }
        $this->schemas[$uri] = $schema;

        return $schema;
    }

}