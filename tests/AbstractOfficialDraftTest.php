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

use Opis\JsonSchema\{Schema, Uri, Validator, SchemaLoader};
use Opis\JsonSchema\Parsers\SchemaParser;
use Opis\JsonSchema\Resolvers\DefaultSchemaResolver;
use Opis\JsonSchema\Errors\ValidationError;
use Opis\JsonSchema\Exceptions\SchemaException;
use PHPUnit\Framework\TestCase;

abstract class AbstractOfficialDraftTest extends TestCase
{

    protected static Validator $validator;

    /**
     * @inheritDoc
     */
    public static function setUpBeforeClass(): void
    {
        $resolver = new DefaultSchemaResolver();

        $resolver->registerFile('http://json-schema.org/draft-06/schema#', __DIR__ . '/official/drafts/draft6.json');
        $resolver->registerFile('http://json-schema.org/draft-07/schema#', __DIR__ . '/official/drafts/draft7.json');
        $resolver->registerPrefix('http://localhost:1234/', __DIR__ . '/official/remotes');

        self::$validator = new Validator(new SchemaLoader(new SchemaParser(), $resolver));
    }

    /**
     * @return string
     */
    abstract protected function getDraft();

    /**
     * @param $schema
     * @param $description
     * @param $data
     * @param $valid
     * @param null $comment
     * @param null $file
     * @param bool $clean
     */
    protected function doValidation($schema, $description, $data, $valid, $comment = null, $file = null, $clean = false)
    {
        $validator = self::$validator;

        if ($clean) {
            $validator->loader()->clearCache();
        }

        try {
            $result = $validator->dataValidation($data, $schema, null, null, null, $this->getDraft());
        } catch (SchemaException $exception) {
            $this->assertFalse($valid, $file . ' -> ' . $description . ': ' . $comment);
            return;
        }

        if ($valid) {
            $this->assertNull($result, $file . ' -> ' . $description . ': ' . $comment);
        } else {
            $this->assertInstanceOf(ValidationError::class, $result, $file . ' -> ' . $description . ': ' . $comment);
        }
    }

    /**
     * @dataProvider basicDataProvider
     */
    public function testBasic(...$args)
    {
        $this->doValidation(...$args);
    }

    /**
     * @dataProvider optionalDataProvider
     */
    public function testOptional(...$args)
    {
        $this->doValidation(...$args);
    }

    public function testSelf()
    {
        $validator = self::$validator;
        $uri = "http://json-schema.org/draft-{$this->getDraft()}/schema#";

        $schema = $validator->loader()->loadSchemaById(Uri::parse($uri));
        $this->assertInstanceOf(Schema::class, $schema);

        $data = $schema->info()->data();

        $this->assertNull($validator->schemaValidation($data, $schema));
    }

    protected function getData($dir)
    {
        $dir = __DIR__ . '/official/tests/draft' . ((int) $this->getDraft()) . '/' . $dir;

        foreach (glob($dir . '*.json') as $file) {
            $data = file_get_contents($file);
            $data = json_decode($data, false);
            $name = explode('/', $file);
            $name = array_pop($name);
            foreach ($data as $schemaTest) {
                if (!isset($schemaTest->tests) || ($schemaTest->skip ?? false)) {
                    continue;
                }

                $clean = true;
                foreach ($schemaTest->tests as $test) {
                    if ($test->skip ?? false) {
                        continue;
                    }

                    yield [$schemaTest->schema, $schemaTest->description ?? '', $test->data, $test->valid, $test->description ?? null, $name, $clean];
                    $clean = false;
                }
            }
        }
    }

    public function basicDataProvider()
    {
        yield from $this->getData('');
    }

    public function optionalDataProvider()
    {
        yield from $this->getData('optional/');
        if ((int)$this->getDraft() > 6) {
            yield from $this->getData('optional/format/');
        }
    }
}