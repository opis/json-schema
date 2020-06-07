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

use InvalidArgumentException;
use Opis\JsonSchema\Errors\IValidationError;
use Opis\JsonSchema\Resolvers\{ISchemaResolver};
use Opis\JsonSchema\Parsers\{ISchemaParser, SchemaParser};

class Validator
{

    protected ISchemaLoader $loader;

    protected int $maxErrors = 1;

    /**
     * @param ISchemaLoader|null $loader
     * @param int $max_errors
     */
    public function __construct(?ISchemaLoader $loader = null, int $max_errors = 1)
    {
        $this->loader = $loader ?? new SchemaLoader(new SchemaParser());
        $this->maxErrors = $max_errors;
    }

    /**
     * @param $data
     * @param Uri|string $uri
     * @param array|null $globals
     * @param array|null $slots
     * @return null|IValidationError
     */
    public function uriValidation($data, $uri, ?array $globals = null, ?array $slots = null): ?IValidationError
    {
        if (is_string($uri)) {
            $uri = Uri::parse($uri, true);
        }

        if (!($uri instanceof Uri)) {
            throw new InvalidArgumentException("Invalid uri");
        }

        $schema = $this->loader->loadSchemaById(Uri::parse($uri, true));

        return $this->schemaValidation($data, $schema, $globals, $slots);
    }

    /**
     * @param $data
     * @param string|object|bool $schema
     * @param array|null $globals
     * @param array|null $slots
     * @param string|null $id
     * @param string|null $draft
     * @return IValidationError|null
     */
    public function dataValidation(
        $data,
        $schema,
        ?array $globals = null,
        ?array $slots = null,
        ?string $id = null,
        ?string $draft = null
    ): ?IValidationError
    {
        if (is_string($schema)) {
            $schema = json_decode($schema, false);
        }

        if ($schema === true) {
            return null;
        }

        if ($schema === false) {
            $schema = $this->loader->loadBooleanSchema(false, $id, $draft);
        } else {
            if (!is_object($schema)) {
                throw new InvalidArgumentException("Invalid schema");
            }

            $schema = $this->loader->loadObjectSchema($schema, $id, $draft);
        }

        return $this->schemaValidation($data, $schema, $globals, $slots);
    }

    /**
     * @param $data
     * @param ISchema $schema
     * @param array|null $globals
     * @param array|null $slots
     * @return null|IValidationError
     */
    public function schemaValidation(
        $data,
        ISchema $schema,
        ?array $globals = null,
        ?array $slots = null
    ): ?IValidationError
    {
        return $schema->validate($this->createContext($data, $globals, $slots));
    }

    /**
     * @param $data
     * @param array $globals
     * @param array $slots
     * @return IContext
     */
    public function createContext($data, ?array $globals = null, ?array $slots = null): IContext
    {
        if ($slots) {
            $slots = $this->parseSlots($slots);
        }

        return new Context($data, $this->loader, null, $globals ?? [], $slots, $this->maxErrors);
    }

    /**
     * @return ISchemaParser
     */
    public function parser(): ISchemaParser
    {
        return $this->loader->parser();
    }

    /**
     * @param ISchemaParser $parser
     * @return Validator
     */
    public function setParser(ISchemaParser $parser): self
    {
        $this->loader->setParser($parser);

        return $this;
    }

    /**
     * @return ISchemaResolver|null
     */
    public function resolver(): ?ISchemaResolver
    {
        return $this->loader->resolver();
    }

    /**
     * @param ISchemaResolver|null $resolver
     * @return Validator
     */
    public function setResolver(?ISchemaResolver $resolver): self
    {
        $this->loader->setResolver($resolver);

        return $this;
    }

    /**
     * @return ISchemaLoader
     */
    public function loader(): ISchemaLoader
    {
        return $this->loader;
    }

    /**
     * @param ISchemaLoader $loader
     * @return Validator
     */
    public function setLoader(ISchemaLoader $loader): self
    {
        $this->loader = $loader;

        return $this;
    }

    /**
     * @return int
     */
    public function getMaxErrors(): int
    {
        return $this->maxErrors;
    }

    /**
     * @param int $max_errors
     * @return Validator
     */
    public function setMaxErrors(int $max_errors): self
    {
        $this->maxErrors = $max_errors;

        return $this;
    }

    /**
     * @param array $slots
     * @return array
     */
    protected function parseSlots(array $slots): array
    {
        foreach ($slots as $name => &$value) {
            if (!is_string($name)) {
                unset($slots[$name]);
                continue;
            }

            if (is_string($value)) {
                $value = Uri::parse($value, true);
            }

            if ($value instanceof Uri) {
                $value = $this->loader->loadSchemaById($value);
            } elseif (is_bool($value)) {
                $value = $this->loader->loadBooleanSchema($value);
            }

            if (!is_object($value)) {
                unset($slots[$name]);
            }

            unset($value);
        }

        return $slots;
    }
}