<?php
/* ===========================================================================
 * Copyright 2018 Zindex Software
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

use Opis\JsonSchema\Exception\AbstractSchemaException;
use Opis\JsonSchema\IValidator;
use Opis\JsonSchema\Loaders\File;
use Opis\JsonSchema\Schema;
use Opis\JsonSchema\Validator;
use PHPUnit\Framework\TestCase;

abstract class AbstractOficialDraft extends TestCase
{

    const URL = 'http://localhost:1234';

    public function testRequired()
    {
        $this->doTests($this->getFolderPath());
    }

    public function testOptional()
    {
        $this->doTests($this->getFolderPath() . '/optional');
        $this->doTests($this->getFolderPath() . '/optional/format');
    }

    protected function getFolderPath(): string
    {
        return __DIR__ . '/official/tests/draft' . $this->getDraft();
    }

    protected function getValidator(): IValidator
    {
        $loader = new File( self::URL, [__DIR__ . "/official/remotes"]);
        $file = __DIR__ . '/official/drafts/draft' . $this->getDraft() . '.json';
        $loader->add(json_decode(file_get_contents($file), false));
        return new Validator(null, $loader);
    }

    protected function doTests(string $folder)
    {

        if (!is_dir($folder)) {
            return;
        }

        $validator = $this->getValidator();

        //return $this->doFileTest($folder . '/definitions.json', $validator);

        foreach (glob($folder . "/*.json") as $file) {
            // echo $file, PHP_EOL;
            $this->doFileTest($file, $validator);
        }
    }

    protected function doFileTest(string $file, IValidator $validator)
    {
        $data = json_decode(file_get_contents($file), false);
        $id = explode(DIRECTORY_SEPARATOR, $file);
        $id = end($id);
        foreach ($data as $testgroup) {
            if (isset($testgroup->skip) && $testgroup->skip) {
                continue;
            }
            $schema = new Schema($testgroup->schema, self::URL . '/' . $id);
            foreach ($testgroup->tests as $test) {
                if (isset($test->skip) && $test->skip) {
                    continue;
                }
                try {
                    $result = $validator->schemaValidation($test->data, $schema);
                    $valid = $result->isValid();
                }
                catch (AbstractSchemaException $e) {
                    $valid = false;
                }

                $this->assertTrue($valid === $test->valid, $testgroup->description . ': ' . $test->description . ' - ' . $id);
            }
        }
    }

    abstract protected function getDraft(): int;
}