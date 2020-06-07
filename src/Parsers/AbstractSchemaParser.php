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

use Opis\JsonSchema\{IKeyword, ISchema, IWrapperKeyword, Uri};
use Opis\JsonSchema\Info\{ISchemaInfo, SchemaInfo};
use Opis\JsonSchema\Exceptions\{ISchemaException, ParseException};
use Opis\JsonSchema\Schemas\{
    BooleanSchema,
    EmptySchema,
    ExceptionSchema,
    ObjectSchema
};
use Opis\JsonSchema\Resolvers\{
    IContentEncodingResolver,
    IFilterResolver,
    IFormatResolver,
    IContentMediaTypeResolver
};

abstract class AbstractSchemaParser implements ISchemaParser
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
        'allowWrappers' => true,
        'allowPragmas' => true,

        'allowDataKeyword' => false,
        'allowKeywordsAlongsideRef' => false,

        'defaultDraft' => '07',

        'varRefKey' => '$ref',
        'varEachKey' => '$each',
        'varDefaultKey' => 'default',
    ];

    protected array $options;

    /** @var IDraft[] */
    protected array $drafts;

    protected array $resolvers;

    /**
     * @param IDraft[] $drafts ;
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
     * @return AbstractSchemaParser
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
     * @return null|IFilterResolver
     */
    public function getFilterResolver(): ?IFilterResolver
    {
        return $this->resolver('$filters', IFilterResolver::class);
    }

    /**
     * @param null|IFilterResolver $resolver
     * @return AbstractSchemaParser
     */
    public function setFilterResolver(?IFilterResolver $resolver): self
    {
        return $this->setResolver('$filters', $resolver);
    }

    /**
     * @return null|IFormatResolver
     */
    public function getFormatResolver(): ?IFormatResolver
    {
        return $this->resolver('format', IFormatResolver::class);
    }

    /**
     * @param IFormatResolver|null $resolver
     * @return AbstractSchemaParser
     */
    public function setFormatResolver(?IFormatResolver $resolver): self
    {
        return $this->setResolver('format', $resolver);
    }

    /**
     * @return null|IContentEncodingResolver
     */
    public function getContentEncodingResolver(): ?IContentEncodingResolver
    {
        return $this->resolver('contentEncoding', IContentEncodingResolver::class);
    }

    /**
     * @param IContentEncodingResolver|null $resolver
     * @return AbstractSchemaParser
     */
    public function setContentEncodingResolver(?IContentEncodingResolver $resolver): self
    {
        return $this->setResolver('contentEncoding', $resolver);
    }

    /**
     * @return null|IContentMediaTypeResolver
     */
    public function getMediaTypeResolver(): ?IContentMediaTypeResolver
    {
        return $this->resolver('contentMediaType', IContentMediaTypeResolver::class);
    }

    /**
     * @param IContentMediaTypeResolver|null $resolver
     * @return AbstractSchemaParser
     */
    public function setMediaTypeResolver(?IContentMediaTypeResolver $resolver): self
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
    ): ?ISchema
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
    public function parseSchema(ISchemaInfo $info): ISchema
    {
        if (is_bool($info->data())) {
            return new BooleanSchema($info);
        }

        try {
            return $this->parseSchemaObject($info);
        } catch (ISchemaException $exception) {
            return new ExceptionSchema($info, $exception);
        }
    }

    /**
     * @param ISchemaInfo $info
     * @return ISchema
     */
    protected function parseSchemaObject(ISchemaInfo $info): ISchema
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

        if ($this->option('allowWrappers')) {
            $wrapper = $this->parseWrapperKeywords($info, $draftObject->wrappers(), $shared);
        } else {
            $wrapper = null;
        }

        return $this->parseSchemaKeywords($info, $wrapper, $draftObject->keywords(), $shared, $hasRef);
    }

    /**
     * @param ISchemaInfo $info
     * @param IWrapperKeywordParser[] $wrappers
     * @param object $shared
     * @return IWrapperKeyword|null
     */
    protected function parseWrapperKeywords(ISchemaInfo $info, array $wrappers, object $shared): ?IWrapperKeyword
    {
        $last = null;

        while ($wrappers) {
            /** @var IWrapperKeywordParser $wrapper */
            $wrapper = array_pop($wrappers);
            if ($wrapper && ($keyword = $wrapper->parse($info, $this, $shared))) {
                $keyword->setNext($last);
                $last = $keyword;
                unset($keyword);
            }
            unset($wrapper);
        }

        return $last;
    }

    /**
     * @param ISchemaInfo $info
     * @param IWrapperKeyword $wrapper
     * @param IKeywordParser[] $parsers
     * @param object $shared
     * @param bool $hasRef
     * @return ISchema
     */
    protected function parseSchemaKeywords(ISchemaInfo $info, ?IWrapperKeyword $wrapper, array $parsers, object $shared, bool $hasRef = false): ISchema
    {
        /** @var IKeyword[] $prepend */
        $prepend = [];
        /** @var IKeyword[] $append */
        $append = [];
        /** @var IKeyword[] $before */
        $before = [];
        /** @var IKeyword[] $after */
        $after = [];
        /** @var IKeyword[][] $types */
        $types = [];

        if ($hasRef) {
            foreach ($parsers as $parser) {
                $kType = $parser->type();

                if ($kType === IKeywordParser::TYPE_APPEND) {
                    $container = &$append;
                } elseif ($kType === IKeywordParser::TYPE_PREPEND) {
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
                    case IKeywordParser::TYPE_PREPEND:
                        $prepend[] = $keyword;
                        break;
                    case IKeywordParser::TYPE_APPEND:
                        $append[] = $keyword;
                        break;
                    case IKeywordParser::TYPE_BEFORE:
                        $before[] = $keyword;
                        break;
                    case IKeywordParser::TYPE_AFTER:
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
            return new EmptySchema($info, $wrapper);
        }

        return new ObjectSchema($info, $wrapper, $types, $before, $after);
    }

    /**
     * @inheritDoc
     */
    public function draft(string $version): ?IDraft
    {
        return $this->drafts[$version] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function addDraft(IDraft $draft): ISchemaParser
    {
        $this->drafts[$draft->version()] = $draft;

        return $this;
    }
}