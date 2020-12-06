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

class ContentMediaTypeKeyword implements Keyword
{
    use ErrorTrait;
    use DecodedContentTrait;

    protected string $name;

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

        $media = $this->resolver->resolve($this->name);

        if ($media !== null) {
            $data = $this->getDecodedContent($context);
            $ok = $media instanceof ContentMediaType
                ? $media->validate($data, $this->name)
                : $media($data, $this->name);
            if ($ok) {
                return null;
            }
            unset($data);
        }

        return $this->error($schema, $context, 'contentMediaType', "The media type of the data must be '@media'", [
            'media' => $this->name,
        ]);
    }
}