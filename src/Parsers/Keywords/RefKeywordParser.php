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

namespace Opis\JsonSchema\Parsers\Keywords;

use Opis\JsonSchema\Info\SchemaInfo;
use Opis\JsonSchema\{Keyword, Schema, JsonPointer, Uri, UriTemplate};
use Opis\JsonSchema\Parsers\{KeywordParser, SchemaParser, VariablesTrait};
use Opis\JsonSchema\Keywords\{PointerRefKeyword, RecursiveRefKeyword, TemplateRefKeyword, URIRefKeyword};

class RefKeywordParser extends KeywordParser
{
    use VariablesTrait;

    protected ?string $recursiveRef = null;
    protected ?string $recursiveAnchor = null;
    protected bool $dynamic = false;

    public function __construct(
        string $keyword,
        ?string $recursiveRef = null,
        ?string $recursiveAnchor = null,
        bool $dynamic = false
    ) {
        parent::__construct($keyword);
        $this->recursiveRef = $recursiveRef;
        $this->recursiveAnchor = $recursiveAnchor;
        $this->dynamic = $dynamic;
    }

    /**
     * @inheritDoc
     */
    public function type(): string
    {
        return self::TYPE_AFTER_REF;
    }

    /**
     * @inheritDoc
     */
    public function parse(SchemaInfo $info, SchemaParser $parser, object $shared): ?Keyword
    {
        $recursive = false;
        $schema = $info->data();

        if ($this->keywordExists($schema)) {
            $ref = $this->keywordValue($schema);
            if (!is_string($ref) || $ref === '') {
                throw $this->keywordException('{keyword} must be a non-empty string', $info);
            }
        } elseif ($this->recursiveRef && $this->keywordExists($schema, $this->recursiveRef)) {
            $ref = $this->keywordValue($schema, $this->recursiveRef);
            if ($this->dynamic) {
                if (!preg_match('/^#[a-z][a-z0-9\\-.:_]*/i', $ref)) {
                    $this->keywordException("{keyword} value is malformed", $info, $this->recursiveRef);
                }
            } elseif ($ref !== '#') {
                $this->keywordException("{keyword} supports only '#' as value", $info, $this->recursiveRef);
            }
            $recursive = true;
        } else {
            return null;
        }

        // Mappers
        $mapper = null;
        if ($parser->option('allowMappers') && property_exists($schema, '$map')) {
            if (!is_object($schema->{'$map'}) && !is_array($schema->{'$map'})) {
                throw $this->keywordException('$map keyword must be an object or an array', $info, '$map');
            }

            if (!empty($schema->{'$map'})) {
                $mapper = $this->createVariables($parser, $schema->{'$map'});
            }
        }

        // Globals
        $globals = null;
        if ($parser->option('allowGlobals') && property_exists($schema, '$globals')) {
            if (!is_object($schema->{'$globals'})) {
                throw $this->keywordException('$globals keyword must be an object', $info, '$globals');
            }

            if (!empty($schema->{'$globals'})) {
                $globals = $this->createVariables($parser, $schema->{'$globals'});
            }
        }

        // Pass slots
        $slots = null;
        if ($parser->option('allowSlots') && property_exists($schema, '$pass')) {
            $slots = $this->parsePassSlots($info, $parser);
        }

        if ($recursive) {
            $ref = $info->idBaseRoot()->resolveRef($ref);
            if ($this->dynamic) {
                return new RecursiveRefKeyword($ref, $mapper, $globals, $slots,
                    $this->recursiveRef, $this->recursiveAnchor, $ref->fragment());
            }
            return new RecursiveRefKeyword($ref, $mapper, $globals, $slots,
                $this->recursiveRef, $this->recursiveAnchor, true);
        }

        if ($ref === '#') {
            return new URIRefKeyword(Uri::merge('#', $info->idBaseRoot()), $mapper, $globals, $slots, $this->keyword);
        }

        if ($parser->option('allowTemplates') && UriTemplate::isTemplate($ref)) {
            $tpl = new UriTemplate($ref);

            if ($tpl->hasPlaceholders()) {
                $vars = null;

                if (property_exists($schema, '$vars')) {
                    if (!is_object($schema->{'$vars'})) {
                        throw $this->keywordException('$vars keyword must be an object', $info, '$vars');
                    }

                    if (!empty($schema->{'$vars'})) {
                        $vars = $this->createVariables($parser, $schema->{'$vars'});
                    }
                }

                return new TemplateRefKeyword($tpl, $vars, $mapper, $globals, $slots, $this->keyword);
            }

            unset($tpl);
        }

        if ($ref[0] === '#') {
            if (($pointer = JsonPointer::parse(substr($ref, 1))) && $pointer->isAbsolute()) {
                return new PointerRefKeyword($pointer, $mapper, $globals, $slots, $this->keyword);
            }
        } elseif (($pointer = JsonPointer::parse($ref)) && $pointer->isRelative()) {
            return new PointerRefKeyword($pointer, $mapper, $globals, $slots, $this->keyword);
        }

        $ref = Uri::merge($ref, $info->idBaseRoot(), true);

        if ($ref === null || !$ref->isAbsolute()) {
            throw $this->keywordException('{keyword} must be a valid uri, uri-reference, uri-template or json-pointer',
                $info);
        }

        return new URIRefKeyword($ref, $mapper, $globals, $slots, $this->keyword);
    }

    /**
     * @param SchemaInfo $info
     * @param SchemaParser $parser
     * @return string[]|object[]|Schema[]
     */
    protected function parsePassSlots(SchemaInfo $info, SchemaParser $parser): ?array
    {
        $schema = $info->data();

        if (!is_object($schema->{'$pass'})) {
            throw $this->keywordException('$pass keyword value must be an object', $info, '$pass');
        }

        return $this->getSlotSchemas($info, $parser, $schema->{'$pass'}, ['$pass']);
    }

    /**
     * @param SchemaInfo $info
     * @param SchemaParser $parser
     * @param object $slots
     * @param array $path
     * @return null
     */
    protected function getSlotSchemas(SchemaInfo $info, SchemaParser $parser, object $slots, array $path): ?array
    {
        $keyword = null;
        if ($path) {
            $keyword = end($path);
            $path = array_merge($info->path(), $path);
        } else {
            $path = $info->path();
        }

        $list = [];

        foreach ($slots as $name => $value) {
            if ($value === null) {
                continue;
            }
            if (is_string($value) || is_object($value)) {
                $list[$name] = $value;
            } elseif (is_bool($value)) {
                $list[$name] = $parser->parseSchema(new SchemaInfo(
                    $value, null, $info->id() ?? $info->base(), $info->root(),
                    array_merge($path, [$name]),
                    $info->draft() ?? $parser->defaultDraftVersion()
                ));
            } else {
                throw $this->keywordException('Slots must contain valid json schemas or slot names', $info, $keyword);
            }
        }

        return $list ?: null;
    }
}