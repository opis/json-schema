<?php
/* ============================================================================
 * Copyright 2020-2021 Zindex Software
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

use Opis\JsonSchema\{CompliantValidator, Schema, Uri};
use Opis\JsonSchema\Errors\ValidationError;
use Opis\JsonSchema\Exceptions\SchemaException;
use PHPUnit\Framework\TestCase;

abstract class AbstractOfficialDraftTest extends TestCase
{
    protected static CompliantValidator $validator;

    /**
     * @inheritDoc
     */
    public static function setUpBeforeClass(): void
    {
        $validator = new CompliantValidator();

        $validator
            ->resolver()
            ->registerPrefix('http://json-schema.org/', __DIR__ . '/official/drafts')
            ->registerPrefix('https://json-schema.org/', __DIR__ . '/official/drafts')
            ->registerPrefix('http://localhost:1234/', __DIR__ . '/official/remotes')
        ;

        self::$validator = $validator;
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

    public function testSelf()
    {
        $validator = self::$validator;

        $draft = $this->getDraft();

        if (in_array($draft, ['06', '07'])) {
            $uri = "http://json-schema.org/draft-{$draft}/schema#";
        } else {
            $uri = "https://json-schema.org/draft/{$draft}/schema#";
        }

        $schema = $validator->loader()->loadSchemaById(Uri::parse($uri));

        $this->assertInstanceOf(Schema::class, $schema);

        $data = $schema->info()->data();

        $this->assertNull($validator->schemaValidation($data, $schema));
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

    protected function getData($dir)
    {
        $dir = __DIR__ . '/official/tests/draft' . $this->getDraft() . '/' . $dir;

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
        yield from $this->getData('optional/format/');
    }
}