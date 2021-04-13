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

use Opis\JsonSchema\Keyword;
use Opis\JsonSchema\Info\SchemaInfo;
use Opis\JsonSchema\Keywords\FiltersKeyword;
use Opis\JsonSchema\Resolvers\FilterResolver;
use Opis\JsonSchema\Parsers\{KeywordParser, SchemaParser,
    ResolverTrait, VariablesTrait};

class FiltersKeywordParser extends KeywordParser
{
    use ResolverTrait;
    use VariablesTrait;

    /**
     * @inheritDoc
     */
    public function type(): string
    {
        return self::TYPE_APPEND;
    }

    /**
     * @inheritDoc
     */
    public function parse(SchemaInfo $info, SchemaParser $parser, object $shared): ?Keyword
    {
        $schema = $info->data();

        if (!$parser->option('allowFilters')) {
            return null;
        }

        $resolver = $parser->getFilterResolver();

        if (!$resolver || !$this->keywordExists($schema)) {
            return null;
        }

        $filters = $this->parseFilters($parser, $resolver, $this->keywordValue($schema), $info);
        if (!$filters) {
            return null;
        }

        return new FiltersKeyword($filters);
    }

    /**
     * @param SchemaParser $parser
     * @param FilterResolver $filterResolver
     * @param mixed $filters
     * @param SchemaInfo $info
     * @return array|null
     */
    protected function parseFilters(
        SchemaParser $parser,
        FilterResolver $filterResolver,
        $filters,
        SchemaInfo $info
    ): ?array
    {
        if (is_string($filters)) {
            if ($filters = $this->parseFilter($parser, $filterResolver, $filters, $info)) {
                return [$filters];
            }

            return null;
        }

        if (is_object($filters)) {
            if ($filter = $this->parseFilter($parser, $filterResolver, $filters, $info)) {
                return [$filter];
            }

            return null;
        }

        if (is_array($filters)) {
            if (!$filters) {
                return null;
            }
            $list = [];
            foreach ($filters as $filter) {
                if ($filter = $this->parseFilter($parser, $filterResolver, $filter, $info)) {
                    $list[] = $filter;
                }
            }

            return $list ?: null;
        }

        throw $this->keywordException('{keyword} can be a non-empty string, an object or an array of string and objects', $info);
    }

    /**
     * @param SchemaParser $parser
     * @param FilterResolver $resolver
     * @param $filter
     * @param SchemaInfo $info
     * @return object|null
     */
    protected function parseFilter(
        SchemaParser $parser,
        FilterResolver $resolver,
        $filter,
        SchemaInfo $info
    ): ?object
    {
        $vars = null;
        if (is_object($filter)) {
            if (!property_exists($filter, '$func') || !is_string($filter->{'$func'}) || $filter->{'$func'} === '') {
                throw $this->keywordException('$func (for {keyword}) must be a non-empty string', $info);
            }

            $vars = get_object_vars($filter);
            unset($vars['$func']);

            if (property_exists($filter, '$vars')) {
                if (!is_object($filter->{'$vars'})) {
                    throw $this->keywordException('$vars (for {keyword}) must be a string', $info);
                }
                unset($vars['$vars']);
                $vars = get_object_vars($filter->{'$vars'}) + $vars;
            }

            $filter = $filter->{'$func'};
        } elseif (!is_string($filter) || $filter === '') {
            throw $this->keywordException('{keyword} can be a non-empty string, an object or an array of string and objects', $info);
        }

        $list = $resolver->resolveAll($filter);
        if (!$list) {
            throw $this->keywordException("{keyword}: {$filter} doesn't exists", $info);
        }

        $list = $this->resolveSubTypes($list);

        return (object)[
            'name' => $filter,
            'args' => $vars ? $this->createVariables($parser, $vars) : null,
            'types' => $list,
        ];
    }
}