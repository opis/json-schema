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

namespace Opis\JsonSchema\Parsers\Drafts;

use Opis\JsonSchema\Parsers\Draft;
use Opis\JsonSchema\Parsers\KeywordParser;
use Opis\JsonSchema\Parsers\Keywords\{AdditionalItemsKeywordParser,
    AdditionalPropertiesKeywordParser,
    AllOfKeywordParser,
    AnyOfKeywordParser,
    ConstKeywordParser,
    ContainsKeywordParser,
    ContentEncodingKeywordParser,
    ContentMediaTypeKeywordParser,
    ContentSchemaKeywordParser,
    DefaultKeywordParser,
    DependenciesKeywordParser,
    DependentRequiredKeywordParser,
    DependentSchemasKeywordParser,
    EnumKeywordParser,
    ExclusiveMaximumKeywordParser,
    ExclusiveMinimumKeywordParser,
    FormatKeywordParser,
    IfThenElseKeywordParser,
    ItemsKeywordParser,
    MaximumKeywordParser,
    MaxItemsKeywordParser,
    MaxLengthKeywordParser,
    MaxPropertiesKeywordParser,
    MinimumKeywordParser,
    MinItemsKeywordParser,
    MinLengthKeywordParser,
    MinPropertiesKeywordParser,
    MultipleOfKeywordParser,
    NotKeywordParser,
    OneOfKeywordParser,
    PatternKeywordParser,
    PatternPropertiesKeywordParser,
    PropertiesKeywordParser,
    PropertyNamesKeywordParser,
    RefKeywordParser,
    RequiredKeywordParser,
    TypeKeywordParser,
    UnevaluatedItemsKeywordParser,
    UnevaluatedPropertiesKeywordParser,
    UniqueItemsKeywordParser
};

class Draft202012 extends Draft
{
    /**
     * @inheritDoc
     */
    public function version(): string
    {
        return '2020-12';
    }

    public function allowKeywordsAlongsideRef(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function supportsAnchorId(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    protected function getRefKeywordParser(): KeywordParser
    {
        return new RefKeywordParser('$ref', [
            ['ref' => '$dynamicRef', 'anchor' => '$dynamicAnchor', 'fragment' => true],
            ['ref' => '$recursiveRef', 'anchor' => '$recursiveAnchor', 'fragment' => false],
        ]);
    }

    /**
     * @inheritDoc
     */
    protected function getKeywordParsers(): array
    {
        return [
            // Generic
            new TypeKeywordParser('type'),
            new ConstKeywordParser('const'),
            new EnumKeywordParser('enum'),
            new FormatKeywordParser('format'),

            // String
            new MinLengthKeywordParser('minLength'),
            new MaxLengthKeywordParser('maxLength'),
            new PatternKeywordParser("pattern"),
            new ContentEncodingKeywordParser('contentEncoding'),
            new ContentMediaTypeKeywordParser('contentMediaType'),
            new ContentSchemaKeywordParser('contentSchema'),

            // Number
            new MinimumKeywordParser('minimum', 'exclusiveMinimum'),
            new MaximumKeywordParser('maximum', 'exclusiveMaximum'),
            new ExclusiveMinimumKeywordParser('exclusiveMinimum'),
            new ExclusiveMaximumKeywordParser('exclusiveMaximum'),
            new MultipleOfKeywordParser('multipleOf'),

            // Array
            new MinItemsKeywordParser('minItems'),
            new MaxItemsKeywordParser('maxItems'),
            new UniqueItemsKeywordParser('uniqueItems'),
            new ContainsKeywordParser('contains', 'minContains', 'maxContains'),
            new ItemsKeywordParser('prefixItems', ItemsKeywordParser::ONLY_ARRAY),
            new ItemsKeywordParser('items', ItemsKeywordParser::ONLY_SCHEMA, 'prefixItems'),
            // keep for draft-2019-09 compatibility
            new AdditionalItemsKeywordParser('additionalItems'),

            // Object
            new MinPropertiesKeywordParser('minProperties'),
            new MaxPropertiesKeywordParser('maxProperties'),
            new RequiredKeywordParser('required'),
            new DependenciesKeywordParser('dependencies'), // keep for draft-07 compatibility
            new DependentRequiredKeywordParser('dependentRequired'),
            new DependentSchemasKeywordParser('dependentSchemas'),
            new PropertyNamesKeywordParser('propertyNames'),
            new PropertiesKeywordParser('properties'),
            new PatternPropertiesKeywordParser('patternProperties'),
            new AdditionalPropertiesKeywordParser('additionalProperties'),

            // Conditionals
            new IfThenElseKeywordParser('if', 'then', 'else'),
            new AnyOfKeywordParser('anyOf'),
            new AllOfKeywordParser('allOf'),
            new OneOfKeywordParser('oneOf'),
            new NotKeywordParser('not'),

            // Unevaluated
            new UnevaluatedPropertiesKeywordParser('unevaluatedProperties'),
            new UnevaluatedItemsKeywordParser('unevaluatedItems'),

            // Optional
            new DefaultKeywordParser('default'),
        ];
    }

}