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

use Opis\JsonSchema\{ValidationContext, Keyword, Schema, ContentEncoding};
use Opis\JsonSchema\Resolvers\ContentEncodingResolver;
use Opis\JsonSchema\Errors\ValidationError;
use Opis\JsonSchema\Exceptions\UnresolvedContentEncodingException;

class ContentEncodingKeyword implements Keyword
{
    use ErrorTrait;

    protected string $name;

    protected ?ContentEncodingResolver $resolver;

    /** @var bool|null|callable|ContentEncoding */
    protected $encoding = false;

    /**
     * @param string $name
     * @param null|ContentEncodingResolver $resolver
     */
    public function __construct(string $name, ?ContentEncodingResolver $resolver = null)
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

        if ($this->encoding === false) {
            $this->encoding = $this->resolver->resolve($this->name);
        }

        if ($this->encoding === null) {
            throw new UnresolvedContentEncodingException($this->name, $schema, $context);
        }

        $result = $this->encoding instanceof ContentEncoding
            ? $this->encoding->decode($context->currentData(), $this->name)
            : ($this->encoding)($context->currentData(), $this->name);

        if ($result === null) {
            return $this->error($schema, $context, 'contentEncoding', "The value must be encoded as '{encoding}'", [
                'encoding' => $this->name,
            ]);
        }

        $context->setDecodedContent($result);

        return null;
    }
}