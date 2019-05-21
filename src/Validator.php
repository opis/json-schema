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

use Opis\JsonSchema\Exception\{
    FilterNotFoundException, InvalidJsonPointerException, InvalidSchemaException,
    SchemaNotFoundException, SchemaKeywordException, UnknownMediaTypeException
};
use stdClass;

class Validator implements IValidator
{
    const BELL = "\x07";

    /** @var IValidatorHelper */
    protected $helper = null;

    /** @var ISchemaLoader|null */
    protected $loader = null;

    /** @var IFilterContainer|null */
    protected $filters = null;

    /** @var IFormatContainer|null */
    protected $formats = null;

    /** @var IMediaTypeContainer|null */
    protected $mediaTypes = null;

    /** @var bool */
    protected $defaultSupport = true;

    /** @var bool */
    protected $varsSupport = true;

    /** @var bool */
    protected $filtersSupport = true;

    /** @var bool */
    protected $mapSupport = true;

    /** @var array */
    protected $globalVars = [];

    /**
     * Validator constructor.
     * @param IValidatorHelper|null $helper
     * @param ISchemaLoader|null $loader
     * @param IFormatContainer|null $formats
     * @param IFilterContainer|null $filters
     * @param IMediaTypeContainer|null $media
     */
    public function __construct(IValidatorHelper $helper = null,
                                ISchemaLoader $loader = null,
                                IFormatContainer $formats = null,
                                IFilterContainer $filters = null,
                                IMediaTypeContainer $media = null)
    {
        $this->helper = $helper ?? new ValidatorHelper();
        $this->formats = $formats ?? new FormatContainer();
        $this->mediaTypes = $media ?? new MediaTypeContainer();
        $this->loader = $loader;
        $this->filters = $filters;
    }

    /**
     * @inheritDoc
     */
    public function schemaValidation($data, ISchema $schema, int $max_errors = 1, ISchemaLoader $loader = null): ValidationResult
    {
        $bag = new ValidationResult($max_errors);

        if ($loader !== null) {
            $default_loader = $this->loader;
            $this->loader = $loader;
            try {
                $this->validateSchema($data, $data, [], [], $schema, $schema->resolve(), $bag);
            } finally {
                $this->loader = $default_loader;
            }
        } else {
            $this->validateSchema($data, $data, [], [], $schema, $schema->resolve(), $bag);
        }

        return $bag;
    }

    /**
     * @inheritDoc
     */
    public function uriValidation($data, string $schema_uri, int $max_errors = 1, ISchemaLoader $loader = null): ValidationResult
    {
        $schema = new stdClass();
        $schema->{'$ref'} = URI::normalize($schema_uri);
        return $this->dataValidation($data, $schema, $max_errors, $loader);
    }

    /**
     * @inheritDoc
     */
    public function dataValidation($data, $schema, int $max_errors = 1, ISchemaLoader $loader = null): ValidationResult
    {
        $schema = is_string($schema) ? Schema::fromJsonString($schema) : new Schema($schema);
        return $this->schemaValidation($data, $schema, $max_errors, $loader);
    }

    /**
     * @inheritDoc
     */
    public function setFilters(IFilterContainer $filters = null): IValidator
    {
        $this->filters = $filters;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @inheritDoc
     */
    public function setFormats(IFormatContainer $formats = null): IValidator
    {
        $this->formats = $formats;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getFormats()
    {
        return $this->formats;
    }

    /**
     * @inheritDoc
     */
    public function setHelper(IValidatorHelper $helper): IValidator
    {
        $this->helper = $helper;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getHelper(): IValidatorHelper
    {
        return $this->helper;
    }

    /**
     * @inheritDoc
     */
    public function setLoader(ISchemaLoader $loader = null): IValidator
    {
        $this->loader = $loader;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getLoader()
    {
        return $this->loader;
    }

    /**
     * @inheritDoc
     */
    public function setMediaType(IMediaTypeContainer $media = null): IValidator
    {
        $this->mediaTypes = $media;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getMediaType()
    {
        return $this->mediaTypes;
    }

    /**
     * Use default keyword from schema
     * @param bool $use_default
     * @return Validator
     */
    public function defaultSupport(bool $use_default): self
    {
        $this->defaultSupport = $use_default;
        return $this;
    }

    /**
     * Checks if default keyword is used
     * @return bool
     */
    public function hasDefaultSupport(): bool
    {
        return $this->defaultSupport;
    }

    /**
     * Use $vars keyword from schema
     * @param bool $vars
     * @return Validator
     */
    public function varsSupport(bool $vars): self
    {
        $this->varsSupport = $vars;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasVarsSupport(): bool
    {
        return $this->varsSupport;
    }

    /**
     * Use $filters keyword from schema
     * @param bool $filters
     * @return Validator
     */
    public function filtersSupport(bool $filters): self
    {
        $this->filtersSupport = $filters;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasFiltersSupport(): bool
    {
        return $this->filtersSupport;
    }

    /**
     * @param bool $map
     * @return Validator
     */
    public function mapSupport(bool $map): self
    {
        $this->mapSupport = $map;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasMapSupport(): bool
    {
        return $this->mapSupport && $this->varsSupport;
    }

    /**
     * @param array $vars
     * @return Validator
     */
    public function setGlobalVars(array $vars): self
    {
        $this->globalVars = $vars;
        return $this;
    }

    /**
     * @return array
     */
    public function getGlobalVars(): array
    {
        return $this->globalVars;
    }

    /**
     * Validates a schema
     * @param $document_data
     * @param $data
     * @param array $data_pointer
     * @param array $parent_data_pointer
     * @param ISchema $document
     * @param $schema
     * @param ValidationResult $bag
     * @return bool
     */
    protected function validateSchema(&$document_data, &$data, array $data_pointer, array $parent_data_pointer, ISchema $document, $schema, ValidationResult $bag): bool
    {
        if (is_bool($schema)) {
            if ($schema) {
                return true;
            }
            $bag->addError(new ValidationError($data, $data_pointer, $parent_data_pointer, $schema, '$schema', [
                'schema' => $schema
            ]));
            return false;
        }

        if (!is_object($schema)) {
            throw new InvalidSchemaException($schema);
        }

        if (property_exists($schema, '$ref') && is_string($schema->{'$ref'})) {
            return $this->validateRef($document_data, $data, $data_pointer, $parent_data_pointer, $document, $schema, $bag);
        }

        return $this->validateKeywords($document_data, $data, $data_pointer, $parent_data_pointer, $document, $schema, $bag);
    }

    /**
     * Resolves $ref property and validates resulted schema
     * @param $document_data
     * @param $data
     * @param array $data_pointer
     * @param array $parent_data_pointer
     * @param ISchema $document
     * @param stdClass $schema
     * @param ValidationResult $bag
     * @return bool
     */
    protected function validateRef(&$document_data, &$data, array $data_pointer, array $parent_data_pointer, ISchema $document, stdClass $schema, ValidationResult $bag): bool
    {
        $ref = $schema->{'$ref'};

        // $vars
        if ($this->varsSupport) {
            $ref = URI::parseTemplate($ref, $this->getVars($document_data, $data_pointer, $schema));
        }

        $map_used = $this->mapSupport && property_exists($schema, Schema::MAP_PROP);

        // $map
        if ($map_used) {
            unset($data);
            $data = $this->deepClone($schema->{Schema::MAP_PROP});
            $this->resolveVars($data,$document_data, $data_pointer);

            unset($document_data);
            $document_data = &$data;

            if ($data_pointer) {
                $parent_data_pointer = array_merge($parent_data_pointer, $data_pointer);
                $data_pointer = [];
            }
        }

        // Check if is relative json pointer
        if ($relative = JsonPointer::parseRelativePointer($ref, true)) {
            if (!JsonPointer::isEscapedPointer($ref)) {
                throw new InvalidJsonPointerException($ref);
            }

            $schema = JsonPointer::getDataByRelativePointer(
                $document->resolve(),
                $relative,
                $schema->{Schema::PATH_PROP} ?? [],
                $this
            );
            if ($schema === $this) {
                throw new InvalidJsonPointerException($ref);
            }

            return $this->validateSchema($document_data, $data, $data_pointer, $parent_data_pointer, $document, $schema, $bag);
        }

        // Check if is a json pointer relative to this document
        if (isset($ref[0]) && $ref[0] === '#') {
            $pointer = substr($ref, 1);
            if (JsonPointer::isPointer($pointer)) {
                if (!JsonPointer::isEscapedPointer($pointer)) {
                    throw new InvalidJsonPointerException($pointer);
                }
                $schema = JsonPointer::getDataByPointer($document->resolve(), $pointer, $this);
                if ($schema === $this) {
                    throw new InvalidJsonPointerException($pointer);
                }

                return $this->validateSchema($document_data, $data, $data_pointer, $parent_data_pointer, $document, $schema, $bag);
            }
            unset($pointer);
        }

        // Merge uris
        $ref = URI::merge($ref, $schema->{Schema::BASE_ID_PROP} ?? '', true);

        list($base_ref, $fragment) = explode('#', $ref, 2);

        if (JsonPointer::isPointer($fragment)) {
            if (!JsonPointer::isEscapedPointer($fragment)) {
                throw new InvalidJsonPointerException($fragment);
            }

            // try to resolve locally
            $schema = $document->resolve($base_ref . '#');

            if ($schema === null) {
                if (!$this->loader) {
                    throw new SchemaNotFoundException($base_ref);
                }
                // use loader
                $document = $this->loader->loadSchema($base_ref);
                if (!($document instanceof ISchema)) {
                    throw new SchemaNotFoundException($base_ref);
                }

                if ($fragment === '' || $fragment === '/') {
                    $schema = $document->resolve();
                } else {
                    $schema = JsonPointer::getDataByPointer($document->resolve(), $fragment, $this);
                    if ($schema === $this) {
                        throw new InvalidJsonPointerException($fragment);
                    }
                }

                if (!$map_used) {
                    unset($document_data);
                    $document_data = &$data;
                    if ($data_pointer) {
                        $parent_data_pointer = array_merge($parent_data_pointer, $data_pointer);
                        $data_pointer = [];
                    }
                }
            } else {
                if ($fragment !== '' && $fragment !== '/') {
                    $schema = JsonPointer::getDataByPointer($schema, $fragment, $this);
                    if ($schema === $this) {
                        throw new InvalidJsonPointerException($fragment);
                    }
                }
            }

            return $this->validateSchema($document_data, $data, $data_pointer, $parent_data_pointer, $document, $schema, $bag);
        }

        // Not a json pointer
        $schema = $document->resolve($ref);
        if ($schema !== null) {
            return $this->validateSchema($document_data, $data, $data_pointer, $parent_data_pointer, $document, $schema, $bag);
        }

        if (!$this->loader) {
            throw new SchemaNotFoundException($base_ref);
        }
        // use loader
        $document = $this->loader->loadSchema($base_ref);
        if (!($document instanceof ISchema)) {
            throw new SchemaNotFoundException($base_ref);
        }

        $schema = $document->resolve($ref);

        if (!$map_used) {
            unset($document_data);
            $document_data = &$data;
            if ($data_pointer) {
                $parent_data_pointer = array_merge($parent_data_pointer, $data_pointer);
                $data_pointer = [];
            }
        }

        return $this->validateSchema($document_data, $data, $data_pointer, $parent_data_pointer, $document, $schema, $bag);
    }

    /**
     * Validates schema keywords
     * @param $document_data
     * @param $data
     * @param array $data_pointer
     * @param array $parent_data_pointer
     * @param ISchema $document
     * @param $schema
     * @param ValidationResult $bag
     * @return bool
     */
    protected function validateKeywords(&$document_data, &$data, array $data_pointer, array $parent_data_pointer, ISchema $document, $schema, ValidationResult $bag): bool
    {
        // here the $ref is already resolved

        $defaults = null;
        // Set defaults if used
        if ($this->defaultSupport && is_object($data) && is_object($schema) && property_exists($schema, 'properties')) {
            foreach ($schema->properties as $property => $value) {
                if (property_exists($data, $property) || !is_object($value) || !property_exists($value, 'default')) {
                    continue;
                }
                $defaults[$property] = $this->deepClone($value->default);
            }
        }

        if (!$this->validateCommons($document_data, $data, $data_pointer, $parent_data_pointer, $document, $schema, $bag, $defaults)) {
            return false;
        }

        if (!$this->validateProperties($document_data, $data, $data_pointer, $parent_data_pointer, $document, $schema, $bag, $defaults)) {
            return false;
        }

        if (!$this->validateConditionals($document_data, $data, $data_pointer, $parent_data_pointer, $document, $schema, $bag)) {
            return false;
        }

        if ($this->filtersSupport && !$this->validateFilters($document_data, $data, $data_pointer, $parent_data_pointer, $document, $schema, $bag)) {
            return false;
        }

        return true;
    }

    /**
     * Validates common keywords
     * @param $document_data
     * @param $data
     * @param array $data_pointer
     * @param array $parent_data_pointer
     * @param ISchema $document
     * @param stdClass $schema
     * @param ValidationResult $bag
     * @param array|null $defaults
     * @return bool
     */
    protected function validateCommons(/** @noinspection PhpUnusedParameterInspection */
        &$document_data, &$data, array $data_pointer, array $parent_data_pointer, ISchema $document, $schema, ValidationResult $bag, array &$defaults = null): bool
    {
        $ok = true;

        // type
        if (property_exists($schema, 'type')) {
            if (is_string($schema->type)) {
                if (!$this->helper->typeExists($schema->type)) {
                    throw new SchemaKeywordException(
                        $schema,
                        'type',
                        $schema->type,
                        "'type' keyword contains unknown value: " . $schema->type
                    );
                }
            } elseif (is_array($schema->type)) {
                /** @noinspection PhpParamsInspection */
                if (count($schema->type) === 0) {
                    throw new SchemaKeywordException(
                        $schema,
                        'type',
                        $schema->type,
                        "'type' keyword must not be an empty array"
                    );
                }
                /** @noinspection PhpParamsInspection */
                if ($schema->type != array_unique($schema->type)) {
                    throw new SchemaKeywordException(
                        $schema,
                        'type',
                        $schema->type,
                        "'type' keyword contains duplicate items"
                    );
                }
                foreach ($schema->type as $type) {
                    if (!is_string($type)) {
                        throw new SchemaKeywordException(
                            $schema,
                            'type',
                            $type,
                            "'type' keyword must have only strings if array, found " . gettype($type)
                        );
                    }
                    if (!$this->helper->typeExists($type)) {
                        throw new SchemaKeywordException(
                            $schema,
                            'type',
                            $type,
                            "'type' keyword contains unknown value: " . $type
                        );
                    }
                }
                unset($type);
            } else {
                throw new SchemaKeywordException(
                    $schema,
                    'type',
                    $schema->type,
                    "'type' keyword must be a string or an array of strings, " . gettype($schema->type) . " given"
                );
            }

            if (!$this->helper->isValidType($data, $schema->type)) {
                $ok = false;
                $bag->addError(new ValidationError($data, $data_pointer, $parent_data_pointer, $schema, 'type', [
                    'expected' => $schema->type,
                    'used' => $this->helper->type($data, true),
                ]));
                if ($bag->isFull()) {
                    return false;
                }
            }
        }

        // const
        if (property_exists($schema, 'const')) {
            if (!$this->helper->equals($data, $schema->const, $defaults)) {
                $ok = false;
                $bag->addError(new ValidationError($data, $data_pointer, $parent_data_pointer, $schema, 'const', [
                    'expected' => $schema->const,
                ]));
                if ($bag->isFull()) {
                    return false;
                }
            }
        }

        // enum
        if (property_exists($schema, 'enum')) {
            if (!is_array($schema->enum)) {
                throw new SchemaKeywordException(
                    $schema,
                    'enum',
                    $schema->enum,
                    "'enum' keyword must be an array, " . gettype($schema->enum) . " given"
                );
            }
            if (count($schema->enum) === 0) {
                throw new SchemaKeywordException(
                    $schema,
                    'enum',
                    $schema->enum,
                    "'enum' keyword must not be empty"
                );
            }

            $found = false;
            foreach ($schema->enum as $v) {
                if ($this->helper->equals($data, $v, $defaults)) {
                    $found = true;
                    break;
                }
            }
            unset($v);
            if (!$found) {
                $ok = false;
                $bag->addError(new ValidationError($data, $data_pointer, $parent_data_pointer, $schema, 'enum', [
                    'expected' => $schema->enum,
                ]));
                if ($bag->isFull()) {
                    return false;
                }
            }
            unset($found);
        }

        return $ok;
    }

    /**
     * Validates conditionals and boolean logic
     * @param $document_data
     * @param $data
     * @param array $data_pointer
     * @param array $parent_data_pointer
     * @param ISchema $document
     * @param stdClass $schema
     * @param ValidationResult $bag
     * @return bool
     */
    protected function validateConditionals(&$document_data, &$data, array $data_pointer, array $parent_data_pointer, ISchema $document, $schema, ValidationResult $bag): bool
    {
        $ok = true;

        // not
        if (property_exists($schema, 'not')) {
            if (!is_bool($schema->not) && !is_object($schema->not)) {
                throw new SchemaKeywordException(
                    $schema,
                    'not',
                    $schema->not,
                    "'not' keyword must be a boolean or an object, " . gettype($schema->not) . " given"
                );
            }

            $newbag = new ValidationResult(1);
            $this->validateSchema($document_data, $data, $data_pointer, $parent_data_pointer, $document, $schema->not, $newbag);
            if (!$newbag->hasErrors()) {
                $ok = false;
                $bag->addError(new ValidationError($data, $data_pointer, $parent_data_pointer, $schema, 'not'));
                if ($bag->isFull()) {
                    return false;
                }
            }
            unset($newbag);
        }

        // if, then, else
        if (property_exists($schema, 'if') && $document->draft() !== '06') {
            if (!is_bool($schema->if) && !is_object($schema->if)) {
                throw new SchemaKeywordException(
                    $schema,
                    'if',
                    $schema->if,
                    "'if' keyword must be a boolean or an object, " . gettype($schema->if) . " given"
                );
            }

            if ($this->validateSchema($document_data, $data, $data_pointer, $parent_data_pointer, $document, $schema->if, new ValidationResult(1))) {
                if (property_exists($schema, 'then')) {
                    if (!is_bool($schema->then) && !is_object($schema->then)) {
                        throw new SchemaKeywordException(
                            $schema,
                            'then',
                            $schema->then,
                            "'then' keyword must be a boolean or an object, " . gettype($schema->then) . " given"
                        );
                    }
                    $newbag = $bag->createByDiff();
                    $this->validateSchema($document_data, $data, $data_pointer, $parent_data_pointer, $document, $schema->then, $newbag);
                    if ($newbag->hasErrors()) {
                        $ok = false;
                        $bag->addError(new ValidationError($data, $data_pointer, $parent_data_pointer, $schema, 'then', [], $newbag->getErrors()));
                        if ($bag->isFull()) {
                            return false;
                        }
                    }
                    unset($newbag);
                }
            } elseif (property_exists($schema, 'else')) {
                if (!is_bool($schema->else) && !is_object($schema->else)) {
                    throw new SchemaKeywordException(
                        $schema,
                        'else',
                        $schema->else,
                        "'else' keyword must be a boolean or an object, " . gettype($schema->then) . " given"
                    );
                }
                $newbag = $bag->createByDiff();
                $this->validateSchema($document_data, $data, $data_pointer, $parent_data_pointer, $document, $schema->else, $newbag);
                if ($newbag->hasErrors()) {
                    $ok = false;
                    $bag->addError(new ValidationError($data, $data_pointer, $parent_data_pointer, $schema, 'else', [], $newbag->getErrors()));
                    if ($bag->isFull()) {
                        return false;
                    }
                }
                unset($newbag);
            }
        }

        // anyOf
        if (property_exists($schema, 'anyOf')) {
            if (!is_array($schema->anyOf)) {
                throw new SchemaKeywordException(
                    $schema,
                    'anyOf',
                    $schema->anyOf,
                    "'anyOf' keyword must be an array, " . gettype($schema->anyOf) . " given"
                );
            }
            if (count($schema->anyOf) === 0) {
                throw new SchemaKeywordException(
                    $schema,
                    'anyOf',
                    $schema->anyOf,
                    "'anyOf' keyword must not be empty"
                );
            }

            $newbag = new ValidationResult(1);
            $valid = false;
            $errors = [];
            foreach ($schema->anyOf as &$one) {
                if (!is_bool($one) && !is_object($one)) {
                    throw new SchemaKeywordException(
                        $schema,
                        'anyOf',
                        $schema->anyOf,
                        "'anyOf' keyword items must be booleans or objects, found " . gettype($one)
                    );
                }
                if ($this->validateSchema($document_data, $data, $data_pointer, $parent_data_pointer, $document, $one, $newbag)) {
                    $valid = true;
                    break;
                }
                $errors = array_merge($errors, $newbag->getErrors());
                $newbag->clear();
            }
            unset($one, $newbag);
            if (!$valid) {
                $ok = false;
                $bag->addError(new ValidationError($data, $data_pointer, $parent_data_pointer, $schema, 'anyOf', [], $errors));
                if ($bag->isFull()) {
                    return false;
                }
            }
            unset($errors);
        }

        // oneOf
        if (property_exists($schema, 'oneOf')) {
            if (!is_array($schema->oneOf)) {
                throw new SchemaKeywordException(
                    $schema,
                    'oneOf',
                    $schema->oneOf,
                    "'oneOf' keyword must be an array, " . gettype($schema->oneOf) . " given"
                );
            }
            if (count($schema->oneOf) === 0) {
                throw new SchemaKeywordException(
                    $schema,
                    'oneOf',
                    $schema->oneOf,
                    "'oneOf' keyword must not be empty"
                );
            }
            $errors = [];
            $newbag = new ValidationResult(1);
            $count = 0;
            foreach ($schema->oneOf as &$one) {
                if (!is_bool($one) && !is_object($one)) {
                    throw new SchemaKeywordException(
                        $schema,
                        'oneOf',
                        $schema->oneOf,
                        "'oneOf' keyword items must be booleans or objects, found " . gettype($one)
                    );
                }
                if ($this->validateSchema($document_data, $data, $data_pointer, $parent_data_pointer, $document, $one, $newbag)) {
                    if (++$count > 1) {
                        break;
                    }
                }
                $errors = array_merge($errors, $newbag->getErrors());
                $newbag->clear();
            }
            unset($one, $newbag);
            if ($count !== 1) {
                $ok = false;
                $bag->addError(new ValidationError($data, $data_pointer, $parent_data_pointer, $schema, 'oneOf', [
                    'matched' => $count,
                ], $errors));
                if ($bag->isFull()) {
                    return false;
                }
            }
            unset($errors);
        }

        // allOf
        if (property_exists($schema, 'allOf')) {
            if (!is_array($schema->allOf)) {
                throw new SchemaKeywordException(
                    $schema,
                    'allOf',
                    $schema->allOf,
                    "'allOf' keyword must be an array, " . gettype($schema->allOf) . " given"
                );
            }
            if (count($schema->allOf) === 0) {
                throw new SchemaKeywordException(
                    $schema,
                    'allOf',
                    $schema->allOf,
                    "'allOf' keyword must not be empty"
                );
            }
            $newbag = $bag->createByDiff();
            $errors = null;
            $valid = true;

            foreach ($schema->allOf as &$one) {
                if (!is_bool($one) && !is_object($one)) {
                    throw new SchemaKeywordException(
                        $schema,
                        'allOf',
                        $schema->allOf,
                        "'allOf' keyword items must be booleans or objects, found " . gettype($one)
                    );
                }
                if (!$this->validateSchema($document_data, $data, $data_pointer, $parent_data_pointer, $document, $one, $newbag)) {
                    $valid = false;
                    $errors = $newbag->getErrors();
                    break;
                }
                $newbag->clear();
            }
            unset($one, $newbag);
            if (!$valid) {
                $ok = false;
                $bag->addError(new ValidationError($data, $data_pointer, $parent_data_pointer, $schema, 'allOf', [], $errors));
                if ($bag->isFull()) {
                    return false;
                }
            }
            unset($errors);
        }

        return $ok;
    }

    /**
     * Validates keywords based on data type
     * @param $document_data
     * @param $data
     * @param array $data_pointer
     * @param array $parent_data_pointer
     * @param ISchema $document
     * @param $schema
     * @param ValidationResult $bag
     * @param array|null $defaults
     * @return bool
     */
    protected function validateProperties(&$document_data, &$data, array $data_pointer, array $parent_data_pointer, ISchema $document, $schema, ValidationResult $bag, array $defaults = null): bool
    {
        $type = $this->helper->type($data, true);
        if ($type === 'null' || $type === 'boolean') {
            return true;
        }

        $valid = false;

        switch ($type) {
            case 'string':
                $valid = $this->validateString($document_data, $data, $data_pointer, $parent_data_pointer, $document, $schema, $bag);
                break;
            case 'number':
            case 'integer':
                $valid = $this->validateNumber($document_data, $data, $data_pointer, $parent_data_pointer, $document, $schema, $bag);
                break;
            case 'array':
                $valid = $this->validateArray($document_data, $data, $data_pointer, $parent_data_pointer, $document, $schema, $bag);
                break;
            case 'object':
                $valid = $this->validateObject($document_data, $data, $data_pointer, $parent_data_pointer, $document, $schema, $bag, $defaults);
                // Setup unused defaults
                if (!$valid && $defaults) {
                    $this->setObjectDefaults($data, $defaults);
                }
                break;
        }

        if (!$valid && $bag->isFull()) {
            return false;
        }

        if (property_exists($schema, 'format') && $this->formats) {
            if (!is_string($schema->format)) {
                throw new SchemaKeywordException(
                    $schema,
                    'format',
                    $schema->format,
                    "'format' keyword must be a string, " . gettype($schema->format) . ", given"
                );
            }
            $formatObj = $this->formats->get($type, $schema->format);
            if ($formatObj === null && $type === 'integer') {
                $formatObj = $this->formats->get('number', $schema->format);
            }
            if ($formatObj !== null) {
                if (!$formatObj->validate($data)) {
                    $valid = false;
                    $bag->addError(new ValidationError($data, $data_pointer, $parent_data_pointer, $schema, 'format', [
                        'type' => $type,
                        'format' => $schema->format,
                    ]));
                    if ($bag->isFull()) {
                        return false;
                    }
                }
            }
            unset($formatObj);
        }

        return $valid;
    }

    /**
     * Validates custom filters
     * @param $document_data
     * @param $data
     * @param array $data_pointer
     * @param array $parent_data_pointer
     * @param ISchema $document
     * @param $schema
     * @param ValidationResult $bag
     * @return bool
     */
    protected function validateFilters(/** @noinspection PhpUnusedParameterInspection */
        &$document_data, &$data, array $data_pointer, array $parent_data_pointer, ISchema $document, $schema, ValidationResult $bag): bool
    {
        if (!property_exists($schema, Schema::FILTERS_PROP) || !$this->filters) {
            return true;
        }

        /** @var array $filters */
        $filters = null;
        if (is_string($schema->{Schema::FILTERS_PROP})) {
            $filters = [(object)[Schema::FUNC_NAME => $schema->{Schema::FILTERS_PROP}]];
        }
        elseif (is_object($schema->{Schema::FILTERS_PROP})) {
            $filters = [$schema->{Schema::FILTERS_PROP}];
        } elseif (is_array($schema->{Schema::FILTERS_PROP})) {
            $filters = $schema->{Schema::FILTERS_PROP};
            if (count($filters) === 0) {
                return true;
            }
            foreach ($filters as &$filter) {
                if (is_string($filter)) {
                    $filter = (object)[Schema::FUNC_NAME => $filter];
                }
            }
            unset($filter);
        } else {
            throw new SchemaKeywordException(
                $schema,
                Schema::FILTERS_PROP,
                $schema->{Schema::FILTERS_PROP},
                "'" . Schema::FILTERS_PROP . "' keyword must be a string, an object or an array of objects, " . gettype($schema->{Schema::FILTERS_PROP}) . " given"
            );
        }

        $type = $this->helper->type($data, true);
        $filter_name = null;
        $valid = true;
        foreach ($filters as $filter) {
            if (!is_object($filter)) {
                throw new SchemaKeywordException(
                    $schema,
                    Schema::FILTERS_PROP,
                    $schema->{Schema::FILTERS_PROP},
                    "'" . Schema::FILTERS_PROP . "' keyword must be a string, an object or an array of objects, found " . gettype($filter)
                );
            }
            if (!property_exists($filter, Schema::FUNC_NAME)) {
                throw new SchemaKeywordException(
                    $filter,
                    Schema::FUNC_NAME,
                    null,
                    "'" . Schema::FUNC_NAME . "' keyword is required"
                );
            }
            if (!is_string($filter->{Schema::FUNC_NAME})) {
                throw new SchemaKeywordException(
                    $filter,
                    Schema::FUNC_NAME,
                    $filter->{Schema::FUNC_NAME},
                    "'" . Schema::FUNC_NAME . "' keyword must be a string, " . gettype($filter->{Schema::FUNC_NAME}) . " given"
                );
            }

            $filterObj = $this->filters->get($type, $filter->{Schema::FUNC_NAME});
            if ($filterObj === null) {
                if ($type === 'integer') {
                    $filterObj = $this->filters->get('number', $filter->{Schema::FUNC_NAME});
                    if ($filterObj === null) {
                        throw new FilterNotFoundException($type, $filter->{Schema::FUNC_NAME});
                    }
                } else {
                    throw new FilterNotFoundException($type, $filter->{Schema::FUNC_NAME});
                }
            }

            if (property_exists($filter, Schema::VARS_PROP)) {
                if (!is_object($filter->{Schema::VARS_PROP})) {
                    throw new SchemaKeywordException(
                        $filter,
                        Schema::VARS_PROP,
                        $filter->{Schema::VARS_PROP},
                        "'" . Schema::VARS_PROP . "' keyword must be an object, " . gettype($filter->{Schema::VARS_PROP}) . " given"
                    );
                }
                $vars = $this->deepClone($filter->{Schema::VARS_PROP});
                $this->resolveVars($vars, $document_data, $data_pointer);
                $vars = (array)$vars;
                if ($this->globalVars) {
                    $vars += $this->globalVars;
                }
            } else {
                $vars = $this->globalVars;
            }

            if (!$filterObj->validate($data, $vars)) {
                $valid = false;
                $filter_name = $filter->{Schema::FUNC_NAME};
                break;
            }

            unset($vars, $filterObj);
        }

        if (!$valid) {
            $bag->addError(new ValidationError($data, $data_pointer, $parent_data_pointer, $schema, Schema::FILTERS_PROP, [
                'type' => $type,
                'filter' => $filter_name,
            ]));
            return false;
        }

        return true;
    }

    /**
     * Validates string keywords
     * @param $document_data
     * @param $data
     * @param array $data_pointer
     * @param array $parent_data_pointer
     * @param ISchema $document
     * @param $schema
     * @param ValidationResult $bag
     * @return bool
     */
    protected function validateString(/** @noinspection PhpUnusedParameterInspection */
        &$document_data, &$data, array $data_pointer, array $parent_data_pointer, ISchema $document, $schema, ValidationResult $bag): bool
    {
        $ok = true;

        // minLength
        if (property_exists($schema, 'minLength')) {
            if (!is_int($schema->minLength)) {
                throw new SchemaKeywordException(
                    $schema,
                    'minLength',
                    $schema->minLength,
                    "'minLength' keyword must be an integer, " . gettype($schema->minLength) . " given"
                );
            }
            if ($schema->minLength < 0) {
                throw new SchemaKeywordException(
                    $schema,
                    'minLength',
                    $schema->minLength,
                    "'minLength' keyword must be positive, " . $schema->minLength . " given"
                );
            }
            $len = $this->helper->stringLength($data);
            if ($len < $schema->minLength) {
                $ok = false;
                $bag->addError(new ValidationError($data, $data_pointer, $parent_data_pointer, $schema, 'minLength', [
                    'min' => $schema->minLength,
                    'length' => $len,
                ]));
                if ($bag->isFull()) {
                    return false;
                }
            }
            unset($len);
        }

        // maxLength
        if (property_exists($schema, 'maxLength')) {
            if (!is_int($schema->maxLength)) {
                throw new SchemaKeywordException(
                    $schema,
                    'maxLength',
                    $schema->maxLength,
                    "'maxLength' keyword must be an integer, " . gettype($schema->maxLength) . " given"
                );
            }
            if ($schema->maxLength < 0) {
                throw new SchemaKeywordException(
                    $schema,
                    'maxLength',
                    $schema->maxLength,
                    "'maxLength' keyword must be positive, " . $schema->maxLength . " given"
                );
            }
            $len = $this->helper->stringLength($data);
            if ($len > $schema->maxLength) {
                $ok = false;
                $bag->addError(new ValidationError($data, $data_pointer, $parent_data_pointer, $schema, 'maxLength', [
                    'max' => $schema->maxLength,
                    'length' => $len,
                ]));
                if ($bag->isFull()) {
                    return false;
                }
            }
            unset($len);
        }

        // pattern
        if (property_exists($schema, 'pattern')) {
            if (!is_string($schema->pattern)) {
                throw new SchemaKeywordException(
                    $schema,
                    'pattern',
                    $schema->pattern,
                    "'pattern' keyword must be a string, " . gettype($schema->pattern) . " given"
                );
            }
            if ($schema->pattern === '') {
                throw new SchemaKeywordException(
                    $schema,
                    'pattern',
                    $schema->pattern,
                    "'pattern' keyword must not be empty"
                );
            }
            $match = @preg_match(self::BELL . $schema->pattern . self::BELL . 'u', $data);
            if ($match === false) {
                throw new SchemaKeywordException(
                    $schema,
                    'pattern',
                    $schema->pattern,
                    "'pattern' keyword must be a valid regex"
                );
            }
            if (!$match) {
                $ok = false;
                $bag->addError(new ValidationError($data, $data_pointer, $parent_data_pointer, $schema, 'pattern', [
                    'pattern' => $schema->pattern,
                ]));
                if ($bag->isFull()) {
                    return false;
                }
            }
        }

        // content encoding
        if (property_exists($schema, 'contentEncoding')) {
            if (!is_string($schema->contentEncoding)) {
                throw new SchemaKeywordException(
                    $schema,
                    'contentEncoding',
                    $schema->contentEncoding,
                    "'contentEncoding' keyword must be a string, " . gettype($schema->contentEncoding) . " given"
                );
            }

            switch ($schema->contentEncoding) {
                case "binary":
                    $decoded = $data;
                    break;
                case "base64":
                    $decoded = base64_decode($data, true);
                    break;
                default:
                    $decoded = false;
                    break;
            }

            if ($decoded === false) {
                $ok = false;
                $bag->addError(new ValidationError($data, $data_pointer, $parent_data_pointer, $schema, 'contentEncoding', [
                    'encoding' => $schema->contentEncoding,
                ]));
                if ($bag->isFull()) {
                    return false;
                }
            }
        }

        // media type
        if (property_exists($schema, 'contentMediaType')) {
            if (!is_string($schema->contentMediaType)) {
                throw new SchemaKeywordException(
                    $schema,
                    'contentMediaType',
                    $schema->contentMediaType,
                    "'contentMediaType' keyword must be a string, " . gettype($schema->contentMediaType) . " given"
                );
            }

            if (!$this->mediaTypes) {
                throw new UnknownMediaTypeException($schema, $schema->contentMediaType);
            }

            if (!isset($decoded)) {
                // is set in contentEncoding if any
                $decoded = $data;
            }

            $valid = false;

            if ($decoded !== false) {
                $media = $this->mediaTypes->resolve($schema->contentMediaType);
                if ($media === null) {
                    throw new UnknownMediaTypeException($schema, $schema->contentMediaType);
                } else {
                    $valid = $media->validate($decoded, $schema->contentMediaType);
                }
            }

            if (!$valid) {
                $ok = false;
                $bag->addError(new ValidationError($data, $data_pointer, $parent_data_pointer, $schema, 'contentMediaType', [
                    'media' => $schema->contentMediaType,
                ]));
                if ($bag->isFull()) {
                    return false;
                }
            }
        }

        return $ok;
    }

    /**
     * Validates number/integer keywords
     * @param $document_data
     * @param $data
     * @param array $data_pointer
     * @param array $parent_data_pointer
     * @param ISchema $document
     * @param $schema
     * @param ValidationResult $bag
     * @return bool
     */
    protected function validateNumber(/** @noinspection PhpUnusedParameterInspection */
        &$document_data, &$data, array $data_pointer, array $parent_data_pointer, ISchema $document, $schema, ValidationResult $bag): bool
    {
        $ok = true;

        // minimum, exclusiveMinimum
        if (property_exists($schema, 'minimum')) {
            if (!is_int($schema->minimum) && !is_float($schema->minimum)) {
                throw new SchemaKeywordException(
                    $schema,
                    'minimum',
                    $schema->minimum,
                    "'minimum' keyword must be an integer or a float, " . gettype($schema->minimum) . " given"
                );
            }

            $exclusive = false;
            if (property_exists($schema, 'exclusiveMinimum')) {
                if (!is_bool($schema->exclusiveMinimum)) {
                    throw new SchemaKeywordException(
                        $schema,
                        'exclusiveMinimum',
                        $schema->exclusiveMinimum,
                        "'exclusiveMinimum' keyword must be a boolean if 'minimum' keyword is present, " . gettype($schema->exclusiveMinimum) . " given"
                    );
                }
                $exclusive = $schema->exclusiveMinimum;
            }

            if ($exclusive && $data == $schema->minimum) {
                $ok = false;
                $bag->addError(new ValidationError($data, $data_pointer, $parent_data_pointer, $schema, 'exclusiveMinimum', [
                    'min' => $schema->minimum
                ]));
                if ($bag->isFull()) {
                    return false;
                }
            } elseif ($data < $schema->minimum) {
                $ok = false;
                $bag->addError(new ValidationError($data, $data_pointer, $parent_data_pointer, $schema, 'minimum', [
                    'min' => $schema->minimum
                ]));
                if ($bag->isFull()) {
                    return false;
                }
            }
        } elseif (property_exists($schema, 'exclusiveMinimum')) {
            if (!is_int($schema->exclusiveMinimum) && !is_float($schema->exclusiveMinimum)) {
                throw new SchemaKeywordException(
                    $schema,
                    'exclusiveMinimum',
                    $schema->exclusiveMinimum,
                    "'exclusiveMinimum' keyword must be an integer or a float if 'minimum' keyword is not present, " . gettype($schema->exclusiveMinimum) . " given"
                );
            }
            if ($data <= $schema->exclusiveMinimum) {
                $ok = false;
                $bag->addError(new ValidationError($data, $data_pointer, $parent_data_pointer, $schema, 'exclusiveMinimum', [
                    'min' => $schema->exclusiveMinimum
                ]));
                if ($bag->isFull()) {
                    return false;
                }
            }
        }

        // maximum, exclusiveMaximum
        if (property_exists($schema, 'maximum')) {
            if (!is_int($schema->maximum) && !is_float($schema->maximum)) {
                throw new SchemaKeywordException(
                    $schema,
                    'maximum',
                    $schema->maximum,
                    "'maximum' keyword must be an integer or a float, " . gettype($schema->maximum) . " given"
                );
            }

            $exclusive = false;
            if (property_exists($schema, 'exclusiveMaximum')) {
                if (!is_bool($schema->exclusiveMaximum)) {
                    throw new SchemaKeywordException(
                        $schema,
                        'exclusiveMaximum',
                        $schema->exclusiveMaximum,
                        "'exclusiveMaximum' keyword must be a boolean is 'maximum' keyword is present, " . gettype($schema->exclusiveMaximum) . " given"
                    );
                }
                $exclusive = $schema->exclusiveMaximum;
            }

            if ($exclusive && $data == $schema->maximum) {
                $ok = false;
                $bag->addError(new ValidationError($data, $data_pointer, $parent_data_pointer, $schema, 'exclusiveMaximum', [
                    'max' => $schema->maximum
                ]));
                if ($bag->isFull()) {
                    return false;
                }
            } elseif ($data > $schema->maximum) {
                $ok = false;
                $bag->addError(new ValidationError($data, $data_pointer, $parent_data_pointer, $schema, 'maximum', [
                    'max' => $schema->maximum
                ]));
                if ($bag->isFull()) {
                    return false;
                }
            }
        } elseif (property_exists($schema, 'exclusiveMaximum')) {
            if (!is_int($schema->exclusiveMaximum) && !is_float($schema->exclusiveMaximum)) {
                throw new SchemaKeywordException(
                    $schema,
                    'exclusiveMaximum',
                    $schema->exclusiveMaximum,
                    "'exclusiveMaximum' keyword must be an integer or a float if 'maximum' keyword is not present, " . gettype($schema->exclusiveMaximum) . " given"
                );
            }
            if ($data >= $schema->exclusiveMaximum) {
                $ok = false;
                $bag->addError(new ValidationError($data, $data_pointer, $parent_data_pointer, $schema, 'exclusiveMaximum', [
                    'max' => $schema->exclusiveMaximum
                ]));
                if ($bag->isFull()) {
                    return false;
                }
            }
        }

        // multipleOf
        if (property_exists($schema, 'multipleOf')) {
            if (!is_int($schema->multipleOf) && !is_float($schema->multipleOf)) {
                throw new SchemaKeywordException(
                    $schema,
                    'multipleOf',
                    $schema->multipleOf,
                    "'multipleOf' keyword must be an integer or a float, " . gettype($schema->multipleOf) . " given"
                );
            }
            if ($schema->multipleOf <= 0) {
                throw new SchemaKeywordException(
                    $schema,
                    'multipleOf',
                    $schema->multipleOf,
                    "'multipleOf' keyword must be greater than 0"
                );
            }
            if (!$this->helper->isMultipleOf($data, $schema->multipleOf)) {
                $ok = false;
                $bag->addError(new ValidationError($data, $data_pointer, $parent_data_pointer, $schema, 'multipleOf', [
                    'divisor' => $schema->multipleOf
                ]));
                if ($bag->isFull()) {
                    return false;
                }
            }
        }

        return $ok;
    }

    /**
     * Validates array keywords
     * @param $document_data
     * @param $data
     * @param array $data_pointer
     * @param array $parent_data_pointer
     * @param ISchema $document
     * @param $schema
     * @param ValidationResult $bag
     * @return bool
     */
    protected function validateArray(&$document_data, &$data, array $data_pointer, array $parent_data_pointer, ISchema $document, $schema, ValidationResult $bag): bool
    {
        $ok = true;

        // minItems
        if (property_exists($schema, 'minItems')) {
            if (!is_int($schema->minItems)) {
                throw new SchemaKeywordException(
                    $schema,
                    'minItems',
                    $schema->minItems,
                    "'minItems' keyword must be an integer, " . gettype($schema->minItems) . " given"
                );
            }
            if ($schema->minItems < 0) {
                throw new SchemaKeywordException(
                    $schema,
                    'minItems',
                    $schema->minItems,
                    "'minItems' keyword must be positive, " . $schema->minItems . " given"
                );
            }
            if (($count = count($data)) < $schema->minItems) {
                $ok = false;
                $bag->addError(new ValidationError($data, $data_pointer, $parent_data_pointer, $schema, 'minItems', [
                    'min' => $schema->minItems,
                    'count' => $count,
                ]));
                if ($bag->isFull()) {
                    return false;
                }
            }
            unset($count);
        }

        // maxItems
        if (property_exists($schema, 'maxItems')) {
            if (!is_int($schema->maxItems)) {
                throw new SchemaKeywordException(
                    $schema,
                    'maxItems',
                    $schema->maxItems,
                    "'maxItems' keyword must be an integer, " . gettype($schema->maxItems) . " given"
                );
            }
            if ($schema->maxItems < 0) {
                throw new SchemaKeywordException(
                    $schema,
                    'maxItems',
                    $schema->maxItems,
                    "'maxItems' keyword must be positive, " . $schema->maxItems . " given"
                );
            }
            if (($count = count($data)) > $schema->maxItems) {
                $ok = false;
                $bag->addError(new ValidationError($data, $data_pointer, $parent_data_pointer, $schema, 'maxItems', [
                    'max' => $schema->maxItems,
                    'count' => $count,
                ]));
                if ($bag->isFull()) {
                    return false;
                }
            }
            unset($count);
        }

        // uniqueItems
        if (property_exists($schema, 'uniqueItems')) {
            if (!is_bool($schema->uniqueItems)) {
                throw new SchemaKeywordException(
                    $schema,
                    'uniqueItems',
                    $schema->uniqueItems,
                    "'uniqueItems' keyword must be a boolean, " . gettype($schema->uniqueItems) . " given"
                );
            }
            if ($schema->uniqueItems) {
                $valid = true;
                $count = count($data);
                $dup = null;
                for ($i = 0; $i < $count - 1; $i++) {
                    for ($j = $i + 1; $j < $count; $j++) {
                        if ($this->helper->equals($data[$i], $data[$j])) {
                            $valid = false;
                            $dup = $data[$i];
                            break 2;
                        }
                    }
                }
                if (!$valid) {
                    $ok = false;
                    $bag->addError(new ValidationError($data, $data_pointer, $parent_data_pointer, $schema, 'uniqueItems', [
                        'duplicate' => $dup,
                    ]));
                    if ($bag->isFull()) {
                        return false;
                    }
                }
                unset($valid, $count, $dup);
            }
        }

        // contains
        if (property_exists($schema, 'contains')) {
            $valid = false;
            $newbag = new ValidationResult(1);
            $errors = [];
            foreach ($data as $i => &$value) {
                $data_pointer[] = $i;
                $valid = $this->validateSchema($document_data, $value, $data_pointer, $parent_data_pointer, $document, $schema->contains, $newbag);
                array_pop($data_pointer);
                if ($valid) {
                    break;
                }
                $errors = array_merge($errors, $newbag->getErrors());
                $newbag->clear();
            }
            unset($value, $newbag);
            if (!$valid) {
                $ok = false;
                $bag->addError(new ValidationError($data, $data_pointer, $parent_data_pointer, $schema, 'contains', [], $errors));
                if ($bag->isFull()) {
                    return false;
                }
            }
            unset($valid, $errors);
        }

        // items, additionalItems
        if (property_exists($schema, 'items')) {
            if (is_array($schema->items)) {
                $count = count($schema->items);
                $data_count = count($data);
                $max = min($count, $data_count);
                for ($i = 0; $i < $max; $i++) {
                    $data_pointer[] = $i;
                    $valid = $this->validateSchema($document_data, $data[$i], $data_pointer, $parent_data_pointer, $document, $schema->items[$i], $bag);
                    array_pop($data_pointer);
                    if (!$valid) {
                        $ok = false;
                        if ($bag->isFull()) {
                            return false;
                        }
                    }
                }
                if ($max < $data_count && property_exists($schema, 'additionalItems')) {
                    if (!is_bool($schema->additionalItems) && !is_object($schema->additionalItems)) {
                        throw new SchemaKeywordException(
                            $schema,
                            'additionalItems',
                            $schema->additionalItems,
                            "'additionalItems' keyword must be a boolean or an object, " . gettype($schema->additionalItems) . " given"
                        );
                    }
                    for ($i = $max; $i < $data_count; $i++) {
                        $data_pointer[] = $i;
                        $valid = $this->validateSchema($document_data, $data[$i], $data_pointer, $parent_data_pointer, $document, $schema->additionalItems, $bag);
                        array_pop($data_pointer);
                        if (!$valid) {
                            $ok = false;
                            if ($bag->isFull()) {
                                return false;
                            }
                        }
                    }
                }
                unset($max, $count, $data_count);
            } else {
                $count = count($data);
                for ($i = 0; $i < $count; $i++) {
                    $data_pointer[] = $i;
                    $valid = $this->validateSchema($document_data, $data[$i], $data_pointer, $parent_data_pointer, $document, $schema->items, $bag);
                    array_pop($data_pointer);
                    if (!$valid) {
                        $ok = false;
                        if ($bag->isFull()) {
                            return false;
                        }
                    }
                }
            }
        }

        return $ok;
    }

    /**
     * Validates object keywords
     * @param $document_data
     * @param $data
     * @param array $data_pointer
     * @param array $parent_data_pointer
     * @param ISchema $document
     * @param $schema
     * @param ValidationResult $bag
     * @param array|null $defaults
     * @return bool
     */
    protected function validateObject(&$document_data, &$data, array $data_pointer, array $parent_data_pointer, ISchema $document, $schema, ValidationResult $bag, array &$defaults = null): bool
    {
        $ok = true;

        // required
        if (property_exists($schema, 'required')) {
            if (!is_array($schema->required)) {
                throw new SchemaKeywordException(
                    $schema,
                    'required',
                    $schema->required,
                    "'required' keyword must be an array, " . gettype($schema->required) . " given"
                );
            }
            foreach ($schema->required as $prop) {
                if (!is_string($prop)) {
                    throw new SchemaKeywordException(
                        $schema,
                        'required',
                        $schema->required,
                        "'required' keyword items must be strings, found " . gettype($prop)
                    );
                }
                if (!property_exists($data, $prop) && !($defaults && array_key_exists($prop, $defaults))) {
                    $ok = false;
                    $bag->addError(new ValidationError($data, $data_pointer, $parent_data_pointer, $schema, 'required', [
                        'missing' => $prop,
                    ]));
                    if ($bag->isFull()) {
                        return false;
                    }
                }
            }
        }

        // dependencies
        if (property_exists($schema, 'dependencies')) {
            if (!is_object($schema->dependencies)) {
                throw new SchemaKeywordException(
                    $schema,
                    'dependencies',
                    $schema->dependencies,
                    "'dependencies' keyword must be an object, " . gettype($schema->dependencies) . " given"
                );
            }
            foreach ($schema->dependencies as $name => &$value) {
                if (!property_exists($data, $name)) {
                    unset($value);
                    continue;
                }
                if (is_array($value)) {
                    foreach ($value as $prop) {
                        if (!is_string($prop)) {
                            throw new SchemaKeywordException(
                                $schema,
                                'dependencies',
                                $schema->dependencies,
                                "'dependencies' keyword items can only be array of strings, objects or booleans, found array with " . gettype($prop)
                            );
                        }
                        if (!property_exists($data, $prop) && !($defaults && array_key_exists($prop, $defaults))) {
                            $ok = false;
                            $bag->addError(new ValidationError($data, $data_pointer, $parent_data_pointer, $schema, 'dependencies', [
                                'missing' => $prop,
                            ]));
                            if ($bag->isFull()) {
                                return false;
                            }
                        }
                    }
                    unset($prop, $value);
                    continue;
                }

                if (!is_bool($value) && !is_object($value)) {
                    throw new SchemaKeywordException(
                        $schema,
                        'dependencies',
                        $schema->dependencies,
                        "'dependencies' keyword items can only be array of strings, objects or booleans, found " . gettype($value)
                    );
                }

                $this->setObjectDefaults($data, $defaults);
                $valid = $this->validateSchema($document_data, $data, $data_pointer, $parent_data_pointer, $document, $value, $bag);
                if (!$valid) {
                    $ok = false;
                    if ($bag->isFull()) {
                        return false;
                    }
                }
                unset($valid, $value);
            }
        }

        $properties = array_map('strval', array_keys(get_object_vars($data)));

        // minProperties
        if (property_exists($schema, 'minProperties')) {
            if (!is_int($schema->minProperties)) {
                throw new SchemaKeywordException(
                    $schema,
                    'minProperties',
                    $schema->minProperties,
                    "'minProperties' keyword must be an integer, " . gettype($schema->minProperties) . " given"
                );
            }
            if ($schema->minProperties < 0) {
                throw new SchemaKeywordException(
                    $schema,
                    'minProperties',
                    $schema->minProperties,
                    "'minProperties' keyword must be positive, " . $schema->minProperties . " given"
                );
            }
            $count = count($properties);
            if ($defaults) {
                $count += count($defaults);
            }
            if ($count < $schema->minProperties) {
                $ok = false;
                $bag->addError(new ValidationError($data, $data_pointer, $parent_data_pointer, $schema, 'minProperties', [
                    'min' => $schema->minProperties,
                    'count' => $count,
                ]));
                if ($bag->isFull()) {
                    return false;
                }
            }
            unset($count);
        }

        // maxProperties
        if (property_exists($schema, 'maxProperties')) {
            if (!is_int($schema->maxProperties)) {
                throw new SchemaKeywordException(
                    $schema,
                    'maxProperties',
                    $schema->maxProperties,
                    "'maxProperties' keyword must be an integer, " . gettype($schema->maxProperties) . " given"
                );
            }
            if ($schema->maxProperties < 0) {
                throw new SchemaKeywordException(
                    $schema,
                    'maxProperties',
                    $schema->maxProperties,
                    "'maxProperties' keyword must be positive, " . $schema->maxProperties . " given"
                );
            }

            $count = count($properties);
            if ($defaults) {
                $count += count($defaults);
            }
            if ($count > $schema->maxProperties) {
                $ok = false;
                $bag->addError(new ValidationError($data, $data_pointer, $parent_data_pointer, $schema, 'maxProperties', [
                    'max' => $schema->maxProperties,
                    'count' => $count,
                ]));
                if ($bag->isFull()) {
                    return false;
                }
            }
            unset($count);
        }

        // propertyNames
        if (property_exists($schema, 'propertyNames')) {
            if (!is_bool($schema->propertyNames) && !is_object($schema->propertyNames)) {
                throw new SchemaKeywordException(
                    $schema,
                    'propertyNames',
                    $schema->propertyNames,
                    "'propertyNames' keyword must be a boolean or an object, " . gettype($schema->propertyNames) . " given"
                );
            }
            $newbag = $bag->createByDiff();
            foreach ($properties as $property) {
                if (!$this->validateSchema($document_data, $property, $data_pointer, $parent_data_pointer, $document, $schema->propertyNames, $newbag)) {
                    $ok = false;
                    $bag->addError(new ValidationError($data, $data_pointer, $parent_data_pointer, $schema, 'propertyNames', [
                        'property' => $property
                    ], $newbag->getErrors()));
                    if ($bag->isFull()) {
                        return false;
                    }
                }
                $newbag->clear();
            }
            unset($newbag);
        }

        $checked_properties = [];

        // properties
        if (property_exists($schema, 'properties')) {
            if (!is_object($schema->properties)) {
                throw new SchemaKeywordException(
                    $schema,
                    'properties',
                    $schema->properties,
                    "'properties' keyword must be an object, " . gettype($schema->properties) . " given"
                );
            }
            foreach ($schema->properties as $name => &$property) {
                if (!is_bool($property) && !is_object($property)) {
                    throw new SchemaKeywordException(
                        $schema,
                        'properties',
                        $schema->properties,
                        "'properties' keyword items must be booleans or objects, found " . gettype($property)
                    );
                }
                $checked_properties[] = $name;
                if (property_exists($data, $name)) {
                    $data_pointer[] = $name;
                    $valid = $this->validateSchema($document_data, $data->{$name}, $data_pointer, $parent_data_pointer, $document, $property, $bag);
                    array_pop($data_pointer);
                    if (!$valid) {
                        $ok = false;
                        if ($bag->isFull()) {
                            return false;
                        }
                    }
                }
                unset($property, $name);
            }
        }

        // patternProperties
        if (property_exists($schema, 'patternProperties')) {
            if (!is_object($schema->patternProperties)) {
                throw new SchemaKeywordException(
                    $schema,
                    'patternProperties',
                    $schema->patternProperties,
                    "'patternProperties' keyword must be an object, " . gettype($schema->patternProperties) . " given"
                );
            }

            $newbag = $bag->createByDiff();
            foreach ($schema->patternProperties as $pattern => &$property_schema) {
                $regex = self::BELL . $pattern . self::BELL . 'u';
                foreach ($properties as $name) {
                    $match = @preg_match($regex, $name);
                    if ($match === false) {
                        throw new SchemaKeywordException(
                            $schema,
                            'patternProperties',
                            $schema->patternProperties,
                            "'patternProperties' keyword must have as properties valid regex expressions, found " . $pattern
                        );
                    }
                    if (!$match) {
                        continue;
                    }
                    if (!in_array($name, $checked_properties)) {
                        $checked_properties[] = $name;
                    }
                    $data_pointer[] = $name;
                    $valid = $this->validateSchema($document_data, $data->{$name}, $data_pointer, $parent_data_pointer, $document, $property_schema, $newbag);
                    array_pop($data_pointer);
                    if (!$valid && $newbag->isFull()) {
                        break 2;
                    }
                }
            }
            unset($property_schema, $regex);
            if ($newbag->hasErrors()) {
                $ok = false;
                $bag->addError(new ValidationError($data, $data_pointer, $parent_data_pointer, $schema, 'patternProperties', [], $newbag->getErrors()));
                if ($bag->isFull()) {
                    return false;
                }
            }
            unset($newbag);
        }

        // additionalProperties
        if (property_exists($schema, 'additionalProperties')) {
            if (!is_bool($schema->additionalProperties) && !is_object($schema->additionalProperties)) {
                throw new SchemaKeywordException(
                    $schema,
                    'additionalProperties',
                    $schema->additionalProperties,
                    "'additionalProperties' keyword must be a boolean or an object, " . gettype($schema->additionalProperties) . " given"
                );
            }
            $newbag = $bag->createByDiff();
            foreach (array_diff($properties, $checked_properties) as $property) {
                $data_pointer[] = $property;
                $valid = $this->validateSchema($document_data, $data->{$property}, $data_pointer, $parent_data_pointer, $document, $schema->additionalProperties, $newbag);
                array_pop($data_pointer);
                unset($property);
                if (!$valid && $newbag->isFull()) {
                    break;
                }
            }
            if ($newbag->hasErrors()) {
                $ok = false;
                $bag->addError(new ValidationError($data, $data_pointer, $parent_data_pointer, $schema, 'additionalProperties', [], $newbag->getErrors()));
                if ($bag->isFull()) {
                    return false;
                }
            }
            unset($newbag);
        }

        // set defaults
        $this->setObjectDefaults($data, $defaults);

        return $ok;
    }

    /**
     * @param $data
     * @param $defaults
     */
    protected function setObjectDefaults($data, array &$defaults = null)
    {
        if (is_object($data) && $defaults) {
            foreach ($defaults as $property => $value) {
                if (!property_exists($data, $property)) {
                    $data->{$property} = $value;
                }
            }
        }
        $defaults = null;
    }

    /**
     * @param $document_data
     * @param $data_pointer
     * @param stdClass $schema
     * @return array
     */
    protected function getVars(&$document_data, &$data_pointer, stdClass $schema): array
    {
        if (!property_exists($schema, Schema::VARS_PROP)) {
            return $this->globalVars;
        }

        if (!is_object($schema->{Schema::VARS_PROP})) {
            throw new SchemaKeywordException(
                $schema,
                Schema::VARS_PROP,
                $schema->{Schema::VARS_PROP},
                "'" . Schema::VARS_PROP . "' keyword must be an object, " . gettype($schema->{Schema::VARS_PROP}) . " given"
            );
        }

        $vars = $this->deepClone($schema->{Schema::VARS_PROP});
        $this->resolveVars($vars, $document_data, $data_pointer);

        $vars = (array) $vars;

        if ($this->globalVars) {
            $vars += $this->globalVars;
        }

        return $vars;
    }

    /**
     * Clones a variable in depth
     * @param $vars
     * @return mixed
     */
    protected function deepClone($vars)
    {
        if (is_object($vars)) {
            $vars = get_object_vars($vars);
            foreach ($vars as $key => $value) {
                if (is_array($value) || is_object($value)) {
                    $vars[$key] = $this->deepClone($value);
                }
                unset($value);
            }
            return (object)$vars;
        }
        if (!is_array($vars)) {
            return $vars;
        }
        foreach ($vars as &$value) {
            if (is_array($value) || is_object($value)) {
                $value = $this->deepClone($value);
            }
            unset($value);
        }
        return $vars;
    }

    /**
     * Resolves $vars
     * @param $vars
     * @param $data
     * @param array $data_pointer
     */
    protected function resolveVars(&$vars, &$data, array &$data_pointer = [])
    {
        if (is_object($vars)) {
            if (property_exists($vars, '$ref') && is_string($vars->{'$ref'})) {
                $ref = $vars->{'$ref'};
                $relative = JsonPointer::parseRelativePointer($ref, true);
                if ($relative === null) {
                    $resolved = JsonPointer::getDataByPointer($data, $ref, $this, false);
                } else {
                    if (!JsonPointer::isEscapedPointer($ref)) {
                        throw new InvalidJsonPointerException($ref);
                    }
                    $resolved = JsonPointer::getDataByRelativePointer($data, $relative, $data_pointer, $this);
                }
                if ($resolved === $this) {
                    throw new InvalidJsonPointerException($ref);
                }
                if (is_array($resolved) && property_exists($vars, '$each') && is_object($vars->{'$each'})) {
                    $pointer = $relative['pointer'] ?? [];
                    foreach ($resolved as $index => &$item) {
                        $copy = $this->deepClone($vars->{'$each'});
                        $pointer[] = $index;
                        $this->resolveVars($copy,$data,$pointer);
                        array_pop($pointer);
                        $item = $copy;
                        unset($copy, $item, $index);
                    }
                    unset($pointer);
                }
                $vars = $resolved;
                return;
            }
        } elseif (!is_array($vars)) {
            return;
        }

        foreach ($vars as $name => &$var) {
            if (is_array($var) || is_object($var)) {
                $this->resolveVars($var, $data, $data_pointer);
            }
            unset($var);
        }
    }

}