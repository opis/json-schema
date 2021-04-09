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

namespace Opis\JsonSchema\Keywords;

use Opis\JsonSchema\{
    ValidationContext,
    Keyword,
    Schema,
    ContentMediaType
};
use Opis\JsonSchema\Errors\ValidationError;
use Opis\JsonSchema\Resolvers\ContentMediaTypeResolver;
use Opis\JsonSchema\Exceptions\UnresolvedContentMediaTypeException;

class ContentMediaTypeKeyword implements Keyword
{
    use ErrorTrait;

    protected string $name;

    /** @var bool|callable|ContentMediaType|null */
    protected $media = false;

    protected ?ContentMediaTypeResolver $resolver;

    /**
     * @param string $name
     * @param null|ContentMediaTypeResolver $resolver
     */
    public function __construct(string $name, ?ContentMediaTypeResolver $resolver)
    {
        $this->name = $name;
        $this->resolver = $resolver;
    }

    /**
     * @inheritDoc
     */
    public function validate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        if (!$this->resolver) {
            return null;
        }

        if ($this->media === false) {
            $this->media = $this->resolver->resolve($this->name);
        }

        if ($this->media === null) {
            throw new UnresolvedContentMediaTypeException($this->name, $schema, $context);
        }

        $data = $context->getDecodedContent();

        $ok = $this->media instanceof ContentMediaType
            ? $this->media->validate($data, $this->name)
            : ($this->media)($data, $this->name);
        if ($ok) {
            return null;
        }

        unset($data);

        return $this->error($schema, $context, 'contentMediaType', "The media type of the data must be '{media}'", [
            'media' => $this->name,
        ]);
    }
}