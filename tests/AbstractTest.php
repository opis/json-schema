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

namespace Opis\JsonSchema\Test;

use Throwable;
use PHPUnit\Framework\TestCase;
use Opis\JsonSchema\Errors\ValidationError;
use Opis\JsonSchema\Exceptions\SchemaException;
use Opis\JsonSchema\Resolvers\SchemaResolver;
use Opis\JsonSchema\{Validator, SchemaLoader};
use Opis\JsonSchema\Parsers\{Vocabulary, SchemaParser};

abstract class AbstractTest extends TestCase
{
    protected static ?Validator $validator = null;

    protected string $currentDraft = '';

    protected static function validator(): Validator
    {
        if (self::$validator === null) {
            $resolver = new SchemaResolver();
            $resolver->registerProtocolDir('file', '', __DIR__ . '/schemas');

            $parser = new SchemaParser(static::parserResolvers(), static::parserOptions(), static::parserVocabulary());

            self::$validator = new Validator(new SchemaLoader($parser, $resolver));
        }

        return self::$validator;
    }

    /**
     * @return array
     */
    protected static function parserOptions(): array
    {
        return [];
    }

    /**
     * @return array
     */
    protected static function parserResolvers(): array
    {
        return [];
    }

    /**
     * @return Vocabulary|null
     */
    protected static function parserVocabulary(): ?Vocabulary
    {
        return null;
    }

    /**
     * @dataProvider draftValidationsProvider
     */
    public function testValidations(string $uri, $data, bool $valid, bool $expectException = false, ?array $globals = null, ?array $slots = null, ?array $skip_drafts = null)
    {
        $validator = self::validator();

        if ($skip_drafts && in_array($validator->parser()->defaultDraftVersion(), $skip_drafts)) {
            $this->assertTrue(true);
            return;
        }

        if ($expectException) {
            $result = null;
            try {
                $result = $validator->uriValidation($data, $uri, $globals ?? [], $slots);
            } catch (Throwable $exception) {
                $this->assertInstanceOf(SchemaException::class, $exception);
                return;
            }
        } else {
            $result = $validator->uriValidation($data, $uri, $globals ?? [], $slots);
        }

        if ($valid) {
            $this->assertNull($result);
        } else {
            $this->assertInstanceOf(ValidationError::class, $result);
        }
    }

    public function draftValidationsProvider(): iterable
    {
        $data = $this->validationsProvider();

        $validator = self::validator();

        $drafts = $validator->parser()->supportedDrafts();

        foreach ($drafts as $draft) {
            $this->currentDraft = $draft;
            $validator->loader()->clearCache();
            $validator->parser()->setDefaultDraftVersion($draft);
            yield from $data;
        }
    }

    /**
     * @return array
     */
    abstract public function validationsProvider(): array;
}
