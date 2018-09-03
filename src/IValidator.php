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

namespace Opis\JsonSchema;

interface IValidator
{

    /**
     * Validates data against ISchema
     * @param $data
     * @param ISchema $schema
     * @param int $max_errors
     * @param ISchemaLoader|null $loader
     * @return ValidationResult
     */
    public function schemaValidation($data, ISchema $schema, int $max_errors = 1, ISchemaLoader $loader = null): ValidationResult;

    /**
     * @param $data
     * @param string $schema_uri
     * @param int $max_errors
     * @param ISchemaLoader|null $loader
     * @return ValidationResult
     */
    public function uriValidation($data, string $schema_uri, int $max_errors = 1, ISchemaLoader $loader = null): ValidationResult;

    /**
     * @param $data
     * @param \stdClass|boolean|string $schema
     * @param int $max_errors
     * @param ISchemaLoader|null $loader
     * @return ValidationResult
     */
    public function dataValidation($data, $schema, int $max_errors = 1, ISchemaLoader $loader = null): ValidationResult;

    /**
     * @param IFilterContainer|null $filters
     * @return IValidator
     */
    public function setFilters(IFilterContainer $filters = null): self;

    /**
     * @return IFilterContainer|null
     */
    public function getFilters();

    /**
     * @param IFormatContainer|null $formats
     * @return IValidator
     */
    public function setFormats(IFormatContainer $formats = null): self;

    /**
     * @return IFilterContainer|null
     */
    public function getFormats();

    /**
     * @param IValidatorHelper $helper
     * @return IValidator
     */
    public function setHelper(IValidatorHelper $helper): self;

    /**
     * @return IValidatorHelper
     */
    public function getHelper(): IValidatorHelper;

    /**
     * @param ISchemaLoader|null $loader
     * @return IValidator
     */
    public function setLoader(ISchemaLoader $loader = null): self;

    /**
     * @return ISchemaLoader|null
     */
    public function getLoader();

    /**
     * @param IMediaTypeContainer|null $media
     * @return IValidator
     */
    public function setMediaType(IMediaTypeContainer $media = null): self;

    /**
     * @return IMediaTypeContainer|null
     */
    public function getMediaType();
}