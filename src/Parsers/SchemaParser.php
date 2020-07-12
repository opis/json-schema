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

namespace Opis\JsonSchema\Parsers;

use Opis\JsonSchema\{Keyword, Schema, KeywordValidator, Uri};
use Opis\JsonSchema\Info\SchemaInfo;
use Opis\JsonSchema\Exceptions\{SchemaException, ParseException};
use Opis\JsonSchema\Schemas\{
    BooleanSchema,
    EmptySchema,
    ExceptionSchema,
    ObjectSchema
};
use Opis\JsonSchema\Resolvers\{
    ContentEncodingResolver,
    FilterResolver,
    FormatResolver,
    ContentMediaTypeResolver
};

abstract class SchemaParser
{
    /** @var string */
    protected const DRAFT_REGEX = '~^https?://json-schema\.org/draft-(\d[0-9-]*\d)/schema#?$~i';

    /** @var array */
    protected const DEFAULT_OPTIONS = [
        'allowFilters' => true,
        'allowMappers' => true,
        'allowTemplates' => true,
        'allowGlobals' => true,
        'allowDefaults' => true,
        'allowSlots' => true,
        'allowContentSchema' => true,
        'allowKeywordValidators' => true,
        'allowPragmas' => true,

        'allowDataKeyword' => false,
        'allowKeywordsAlongsideRef' => false,

        'defaultDraft' => '07',

        'varRefKey' => '$ref',
        'varEachKey' => '$each',
        'varDefaultKey' => 'default',
    ];

    protected array $options;

    /** @var Draft[] */
    protected array $drafts;

    protected array $resolvers;

    /**
     * @param Draft[] $drafts ;
     * @param array|null $resolvers
     * @param array|null $options
     */
    public function __construct(array $drafts, ?array $resolvers = null, ?array $options = null)
    {
        $this->drafts = $drafts;

        $this->resolvers = $resolvers ?? [];

        if ($options) {
            $this->options = $options + self::DEFAULT_OPTIONS;
        } else {
            $this->options = self::DEFAULT_OPTIONS;
        }
    }

    /**
     * @inheritDoc
     */
    public function option(string $name, $default = null)
    {
        return $this->options[$name] ?? $default;
    }

    /**
     * @inheritDoc
     */
    public function setOption(string $name, $value): self
    {
        $this->options[$name] = $value;

        return $this;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param array $options
     * @return SchemaParser
     */
    public function setOptions(array $options): self
    {
        $this->options = $options + $this->options;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function resolver(string $name, ?string $class = null)
    {
        $resolver = $this->resolvers[$name] ?? null;
        if (!is_object($resolver)) {
            return null;
        }

        if ($class !== null) {
            if (!is_subclass_of($resolver, $class, true)) {
                return null;
            }
        }

        return $resolver;
    }

    /**
     * @inheritDoc
     * @return self
     */
    public function setResolver(string $name, $resolver): self
    {
        $this->resolvers[$name] = $resolver;

        return $this;
    }

    /**
     * @return null|FilterResolver
     */
    public function getFilterResolver(): ?FilterResolver
    {
        return $this->resolver('$filters', FilterResolver::class);
    }

    /**
     * @param null|FilterResolver $resolver
     * @return SchemaParser
     */
    public function setFilterResolver(?FilterResolver $resolver): self
    {
        return $this->setResolver('$filters', $resolver);
    }

    /**
     * @return null|FormatResolver
     */
    public function getFormatResolver(): ?FormatResolver
    {
        return $this->resolver('format', FormatResolver::class);
    }

    /**
     * @param FormatResolver|null $resolver
     * @return SchemaParser
     */
    public function setFormatResolver(?FormatResolver $resolver): self
    {
        return $this->setResolver('format', $resolver);
    }

    /**
     * @return null|ContentEncodingResolver
     */
    public function getContentEncodingResolver(): ?ContentEncodingResolver
    {
        return $this->resolver('contentEncoding', ContentEncodingResolver::class);
    }

    /**
     * @param ContentEncodingResolver|null $resolver
     * @return SchemaParser
     */
    public function setContentEncodingResolver(?ContentEncodingResolver $resolver): self
    {
        return $this->setResolver('contentEncoding', $resolver);
    }

    /**
     * @return null|ContentMediaTypeResolver
     */
    public function getMediaTypeResolver(): ?ContentMediaTypeResolver
    {
        return $this->resolver('contentMediaType', ContentMediaTypeResolver::class);
    }

    /**
     * @param ContentMediaTypeResolver|null $resolver
     * @return SchemaParser
     */
    public function setMediaTypeResolver(?ContentMediaTypeResolver $resolver): self
    {
        return $this->setResolver('contentMediaType', $resolver);
    }

    /**
     * @inheritDoc
     */
    public function defaultDraftVersion(): string
    {
        return $this->option('defaultDraft', '07');
    }

    /**
     * @inheritDoc
     */
    public function setDefaultDraftVersion(string $draft): self
    {
        return $this->setOption('defaultDraft', $draft);
    }

    /**
     * @inheritDoc
     */
    public function parseDraftVersion(string $schema): ?string
    {
        if (!preg_match(self::DRAFT_REGEX, $schema, $m)) {
            return null;
        }

        return $m[1] ?? null;
    }

    /**
     * @inheritdoc
     */
    public function parseSchemaId(object $schema, ?Uri $base = null): ?Uri
    {
        if (!property_exists($schema, '$id') || !is_string($schema->{'$id'})) {
            return null;
        }

        return Uri::merge($schema->{'$id'}, $base, true);
    }

    /**
     * @inheritdoc
     */
    public function parseSchemaDraft(object $schema): ?string
    {
        if (!property_exists($schema, '$schema') || !is_string($schema->{'$schema'})) {
            return null;
        }

        return $this->parseDraftVersion($schema->{'$schema'});
    }

    /**
     * @inheritdoc
     */
    public function parseRootSchema(
        object $schema,
        Uri $id,
        callable $handle_id,
        callable $handle_object,
        ?string $draft = null
    ): ?Schema
    {
        $existent = false;
        if (property_exists($schema, '$id')) {
            $existent = true;
            $id = Uri::parse($schema->{'$id'}, true);
        }

        if ($id instanceof Uri) {
            if ($id->fragment() === null) {
                $id = Uri::merge($id, null, true);
            }
        } else {
            throw new ParseException('Root schema id must be an URI', new SchemaInfo($schema, $id));
        }

        if (!$id->isAbsolute()) {
            throw new ParseException('Root schema id must be an absolute URI', new SchemaInfo($schema, $id));
        }

        if ($id->fragment() !== '') {
            throw new ParseException('Root schema id must have an empty fragment or none', new SchemaInfo($schema, $id));
        }

        // Check if id exists
        if ($resolved = $handle_id($id)) {
            return $resolved;
        }

        if (property_exists($schema, '$schema')) {
            if (!is_string($schema->{'$schema'})) {
                throw new ParseException('Schema draft must be a string', new SchemaInfo($schema, $id));
            }
            $draft = $this->parseDraftVersion($schema->{'$schema'});
        }

        if ($draft === null) {
            $draft = $this->defaultDraftVersion();
        }

        if (!$existent) {
            $schema->{'$id'} = (string)$id;
        }

        $resolved = $handle_object($schema, $id, $draft);

        if (!$existent) {
            unset($schema->{'$id'});
        }

        return $resolved;
    }

    /**
     * @inheritDoc
     */
    public function parseSchema(SchemaInfo $info): Schema
    {
        if (is_bool($info->data())) {
            return new BooleanSchema($info);
        }

        try {
            return $this->parseSchemaObject($info);
        } catch (SchemaException $exception) {
            return new ExceptionSchema($info, $exception);
        }
    }

    /**
     * @param SchemaInfo $info
     * @return Schema
     */
    protected function parseSchemaObject(SchemaInfo $info): Schema
    {
        $draftObject = $this->draft($info->draft());

        if ($draftObject === null) {
            throw new ParseException("Unsupported draft-{$info->draft()}", $info);
        }

        /** @var object $schema */
        $schema = $info->data();

        // Check id
        if (property_exists($schema, '$id')) {
            $id = $info->id();
            if ($id === null || !$id->isAbsolute()) {
                throw new ParseException('Schema id must be a valid URI', $info);
            }
        }

        if ($hasRef = property_exists($schema, '$ref')) {
            if ($this->option('allowKeywordsAlongsideRef') || $draftObject->allowKeywordsAlongsideRef()) {
                $hasRef = false;
            }
        }

        $shared = (object) [];

        if ($this->option('allowKeywordValidators')) {
            $keywordValidator = $this->parseKeywordValidators($info, $draftObject->keywordValidators(), $shared);
        } else {
            $keywordValidator = null;
        }

        return $this->parseSchemaKeywords($info, $keywordValidator, $draftObject->keywords(), $shared, $hasRef);
    }

    /**
     * @param SchemaInfo $info
     * @param KeywordValidatorParser[] $keywordValidators
     * @param object $shared
     * @return KeywordValidator|null
     */
    protected function parseKeywordValidators(SchemaInfo $info, array $keywordValidators, object $shared): ?KeywordValidator
    {
        $last = null;

        while ($keywordValidators) {
            /** @var KeywordValidatorParser $keywordValidator */
            $keywordValidator = array_pop($keywordValidators);
            if ($keywordValidator && ($keyword = $keywordValidator->parse($info, $this, $shared))) {
                $keyword->setNext($last);
                $last = $keyword;
                unset($keyword);
            }
            unset($keywordValidator);
        }

        return $last;
    }

    /**
     * @param SchemaInfo $info
     * @param KeywordValidator $keywordValidator
     * @param KeywordParser[] $parsers
     * @param object $shared
     * @param bool $hasRef
     * @return Schema
     */
    protected function parseSchemaKeywords(SchemaInfo $info, ?KeywordValidator $keywordValidator, array $parsers, object $shared, bool $hasRef = false): Schema
    {
        /** @var Keyword[] $prepend */
        $prepend = [];
        /** @var Keyword[] $append */
        $append = [];
        /** @var Keyword[] $before */
        $before = [];
        /** @var Keyword[] $after */
        $after = [];
        /** @var Keyword[][] $types */
        $types = [];

        if ($hasRef) {
            foreach ($parsers as $parser) {
                $kType = $parser->type();

                if ($kType === KeywordParser::TYPE_APPEND) {
                    $container = &$append;
                } elseif ($kType === KeywordParser::TYPE_PREPEND) {
                    $container = &$prepend;
                } else {
                    continue;
                }

                if ($keyword = $parser->parse($info, $this, $shared)) {
                    $container[] = $keyword;
                }

                unset($container, $keyword, $kType);
            }
        } else {
            foreach ($parsers as $parser) {
                $keyword = $parser->parse($info, $this, $shared);
                if ($keyword === null) {
                    continue;
                }

                $kType = $parser->type();

                switch ($kType) {
                    case KeywordParser::TYPE_PREPEND:
                        $prepend[] = $keyword;
                        break;
                    case KeywordParser::TYPE_APPEND:
                        $append[] = $keyword;
                        break;
                    case KeywordParser::TYPE_BEFORE:
                        $before[] = $keyword;
                        break;
                    case KeywordParser::TYPE_AFTER:
                        $after[] = $keyword;
                        break;
                    default:
                        if (!isset($types[$kType])) {
                            $types[$kType] = [];
                        }
                        $types[$kType][] = $keyword;
                        break;

                }
            }
        }

        unset($shared);

        if ($prepend) {
            $before = array_merge($prepend, $before);
        }
        unset($prepend);

        if ($append) {
            $after = array_merge($after, $append);
        }
        unset($append);

        if (empty($before)) {
            $before = null;
        }
        if (empty($after)) {
            $after = null;
        }
        if (empty($types)) {
            $types = null;
        }

        if (empty($types) && empty($before) && empty($after)) {
            return new EmptySchema($info, $keywordValidator);
        }

        return new ObjectSchema($info, $keywordValidator, $types, $before, $after);
    }

    /**
     * @inheritDoc
     */
    public function draft(string $version): ?Draft
    {
        return $this->drafts[$version] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function addDraft(Draft $draft): SchemaParser
    {
        $this->drafts[$draft->version()] = $draft;

        return $this;
    }
}