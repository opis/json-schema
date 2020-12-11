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

use Opis\JsonSchema\Uri;

class SchemaResolver
{
    /** @var bool[]|object[] */
    protected array $raw = [];

    /** @var string[] */
    protected array $files = [];

    /** @var callable[] */
    protected array $protocols = [];

    /** @var array */
    protected array $prefixes = [];

    /** @var array */
    protected array $dirs = [];

    /**
     * @param Uri $uri
     * @return bool|mixed|object|null
     */
    public function resolve(Uri $uri)
    {
        if (!$uri->isAbsolute()) {
            return null;
        }

        $scheme = $uri->scheme();
        if (isset($this->protocols[$scheme])) {
            return ($this->protocols[$scheme])($uri);
        }

        $id = (string) $uri;
        if (isset($this->raw[$id])) {
            return $this->raw[$id];
        }

        $path = $this->resolvePath($uri);

        if ($path === null || !is_file($path)) {
            return null;
        }

        $data = file_get_contents($path);
        if (!is_string($data)) {
            return null;
        }

        $data = json_decode($data, false);

        return $data;
    }

    /**
     * @param bool|object|string $schema
     * @param string|null $id
     * @return bool
     */
    public function registerRaw($schema, ?string $id = null): bool
    {
        if (is_string($schema)) {
            $schema = json_decode($schema, false);
        }

        if ($id !== null && strpos($id, '#') === false) {
            $id .= '#';
        }

        if (is_bool($schema)) {
            if ($id === null) {
                return false;
            }
            $this->raw[$id] = $schema;
            return true;
        }

        if (!is_object($schema)) {
            return false;
        }


        if ($id === null) {
            if (!isset($schema->{'$id'}) || !is_string($schema->{'$id'})) {
                return false;
            }

            $id = $schema->{'$id'};
            if (strpos($id, '#') === false) {
                $id .= '#';
            }
        }

        $this->raw[$id] = $schema;

        return true;
    }

    /**
     * @param string $id
     * @return bool
     */
    public function unregisterRaw(string $id): bool
    {
        if (strpos($id, '#') === false) {
            $id .= '#';
        }

        if (isset($this->raw[$id])) {
            unset($this->raw[$id]);
            return true;
        }

        return false;
    }

    /**
     * @param string $id
     * @param string $file
     * @return SchemaResolver
     */
    public function registerFile(string $id, string $file): self
    {
        if (strpos($id, '#') === false) {
            $id .= '#';
        }

        $this->files[$id] = $file;

        return $this;
    }

    /**
     * @param string $id
     * @return bool
     */
    public function unregisterFile(string $id): bool
    {
        if (strpos($id, '#') === false) {
            $id .= '#';
        }

        if (!isset($this->files[$id])) {
            return false;
        }

        unset($this->files[$id]);

        return true;
    }

    /**
     * @param string $scheme
     * @param callable $handler
     * @return SchemaResolver
     */
    public function registerProtocol(string $scheme, callable $handler): self
    {
        $this->protocols[$scheme] = $handler;

        return $this;
    }

    /**
     * @param string $scheme
     * @return bool
     */
    public function unregisterProtocol(string $scheme): bool
    {
        if (isset($this->protocols[$scheme])) {
            unset($this->protocols[$scheme]);

            return true;
        }

        return false;
    }

    /**
     * @param string $scheme
     * @param string $host
     * @param string|null $dir
     * @return SchemaResolver
     */
    public function registerProtocolDir(string $scheme, string $host, ?string $dir): self
    {
        if ($dir === null) {
            unset($this->dirs[$scheme][$host]);
        } else {
            $this->dirs[$scheme][$host] = rtrim($dir, '/');
        }

        return $this;
    }

    /**
     * @param string $scheme
     * @return bool
     */
    public function unregisterProtocolDirs(string $scheme): bool
    {
        if (isset($this->dirs[$scheme])) {
            unset($this->dirs[$scheme]);

            return true;
        }

        return false;
    }

    /**
     * @param string $prefix
     * @param string $dir
     * @return SchemaResolver
     */
    public function registerPrefix(string $prefix, string $dir): self
    {
        $this->prefixes[$prefix] = rtrim($dir, '/');

        // Sort
        uksort($this->prefixes, [$this, 'sortPrefixKeys']);

        return $this;
    }

    /**
     * @param string $prefix
     * @return SchemaResolver
     */
    public function unregisterPrefix(string $prefix): self
    {
        if (isset($this->prefixes[$prefix])) {
            unset($this->prefixes[$prefix]);
            // Sort
            uksort($this->prefixes, [$this, 'sortPrefixKeys']);
        }

        return $this;
    }


    public function __serialize(): array
    {
        return [
            'raw' => $this->raw,
            'protocols' => $this->protocols,
            'prefixes' => $this->prefixes,
            'dirs' => $this->dirs,
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->raw = $data['raw'];
        $this->protocols = $data['protocols'];
        $this->prefixes = $data['prefixes'];
        $this->dirs = $data['dirs'];
    }

    /**
     * @param string $a
     * @param string $b
     * @return int
     */
    protected function sortPrefixKeys(string $a, string $b): int
    {
        $la = strlen($a);
        $lb = strlen($b);

        if ($lb > $la) {
            return 1;
        }

        if ($lb === $la) {
            return $b < $a ? 1 : ($b === $a ? 0 : -1);
        }

        return -1;
    }

    /**
     * @param Uri $uri
     * @return string|null
     */
    protected function resolvePath(Uri $uri): ?string
    {
        $id = (string)$uri;

        if (isset($this->files[$id])) {
            return $this->files[$id];
        }

        $scheme = $uri->scheme();

        if (isset($this->dirs[$scheme])) {
            $host = (string)$uri->host();
            if (isset($this->dirs[$scheme][$host])) {
                return $this->dirs[$scheme][$host] . '/' . ltrim($uri->path(), '/');
            }
            unset($host);
        }

        $path = null;
        foreach ($this->prefixes as $prefix => $dir) {
            if ($prefix === '' || strpos($id, $prefix) === 0) {
                $path = substr($id, strlen($prefix));
                if ($path === false || $path === '') {
                    $path = null;
                    continue;
                }
                $path = Uri::parseComponents($path);
                if ($path && isset($path['path'])) {
                    $path = $dir . '/' . ltrim($path['path'], '/');
                    break;
                }
                $path = null;
            }
        }

        return $path;
    }
}