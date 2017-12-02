<?php
/* ===========================================================================
 * Copyright 2014-2017 The Opis Project
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

use Opis\JsonSchema\ISchemaLoader;
use Opis\JsonSchema\Schema;

class File implements ISchemaLoader
{

    /** @var string */
    protected $base;

    /** @var string */
    protected $prefix;

    /** @var int */
    protected $prefixLength;

    /** @var array */
    protected $cache = [];

    /**
     * File constructor.
     * @param string $base_dir
     * @param string $prefix
     */
    public function __construct(string $base_dir, string $prefix)
    {
        $this->base = rtrim($base_dir, '/');
        $this->prefix = $prefix;
        $this->prefixLength = strlen($prefix);
    }

    /**
     * @inheritDoc
     */
    public function loadSchema(string $uri)
    {
        if (strpos($uri, $this->prefix) !== 0) {
            return null;
        }
        $uri = substr($uri, $this->prefixLength);
        if (!array_key_exists($uri, $this->cache)) {
            $file = $this->base . $uri;
            $schema = null;
            if (file_exists($file)) {
                $schema = json_decode(file_get_contents($file), false);
                $schema = new Schema($schema, $this->prefix . $uri);
            }
            $this->cache[$uri] = $schema;
        }
        return $this->cache[$uri];
    }

}