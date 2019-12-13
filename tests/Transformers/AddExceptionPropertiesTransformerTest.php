<?php

namespace ShiftOneLabs\PhpRollbarExtensions\Tests\Transformers;

use Error;
use Exception;
use Rollbar\Config;
use Rollbar\Payload\Level;
use Rollbar\Payload\Payload;
use ShiftOneLabs\PhpRollbarExtensions\Tests\TestCase;
use ShiftOneLabs\PhpRollbarExtensions\Tests\Fakes\TestPropertiesTransformerException;
use ShiftOneLabs\PhpRollbarExtensions\Transformers\AddExceptionPropertiesTransformer;

class AddExceptionPropertiesTransformerTest extends TestCase
{
    protected $level = Level::INFO;
    protected $context = [];
    protected $config;

    public function __construct()
    {
        $this->config = new Config([
            'access_token' => $this->getTestAccessToken(),
            'environment' => $this->env,
            'transformer' => AddExceptionPropertiesTransformer::class
        ]);
    }

    public function test_string_log_is_not_transformed()
    {
        $toLog = 'Test log message';

        $payload = $this->buildPayload($toLog);
        $baseCustom = $payload->getData()->getCustom();
        $payload = $this->config->transform($payload, $this->level, $toLog, $this->context);

        $this->assertEquals($baseCustom, $payload->getData()->getCustom());
    }

    public function test_base_exception_is_transformed()
    {
        $toLog = new Exception('Test log message');

        $payload = $this->buildPayload($toLog);
        $payload = $this->config->transform($payload, $this->level, $toLog, $this->context);
        $custom = $payload->getData()->getCustom();

        // Make sure the "properties" key is added to the custom data.
        $this->assertArrayHasKey('properties', $custom);
        $this->assertInternalType('array', $custom['properties']);

        $properties = $custom['properties'];

        // Explicit test for explicit exclusions.
        $this->assertArrayNotHasKey('string', $properties);
        $this->assertArrayNotHasKey('trace', $properties);
        $this->assertArrayNotHasKey('previous', $properties);

        // Make sure the properties key contains the base properties of the
        // exception, excluding the string, trace, and previous values.
        $this->assertEquals([
            'message' => $toLog->getMessage(),
            'code' => $toLog->getCode(),
            'file' => $toLog->getFile(),
            'line' => $toLog->getLine(),
        ], $properties);
    }

    public function test_base_error_is_transformed()
    {
        $toLog = new Error('Test log message');

        $payload = $this->buildPayload($toLog);
        $payload = $this->config->transform($payload, $this->level, $toLog, $this->context);
        $custom = $payload->getData()->getCustom();

        // Make sure the "properties" key is added to the custom data.
        $this->assertArrayHasKey('properties', $custom);
        $this->assertInternalType('array', $custom['properties']);

        $properties = $custom['properties'];

        // Explicit test for explicit exclusions.
        $this->assertArrayNotHasKey('string', $properties);
        $this->assertArrayNotHasKey('trace', $properties);
        $this->assertArrayNotHasKey('previous', $properties);

        // Make sure the properties key contains the base properties of the
        // error, excluding the string, trace, and previous values.
        $this->assertEquals([
            'message' => $toLog->getMessage(),
            'code' => $toLog->getCode(),
            'file' => $toLog->getFile(),
            'line' => $toLog->getLine(),
        ], $properties);
    }

    public function test_all_properties_are_included()
    {
        $toLog = new TestPropertiesTransformerException('Test log message');

        $payload = $this->buildPayload($toLog);
        $payload = $this->config->transform($payload, $this->level, $toLog, $this->context);
        $custom = $payload->getData()->getCustom();

        $this->assertEquals(
            array_merge(
                [
                    'message' => $toLog->getMessage(),
                    'code' => $toLog->getCode(),
                    'file' => $toLog->getFile(),
                    'line' => $toLog->getLine(),
                ],
                $toLog->toArray()
            ),
            $custom['properties']
        );
    }

    public function test_dynamic_properties_are_included()
    {
        $toLog = new Exception('Test log message');
        $toLog->dynamicProperty = 'dynamic property';

        $payload = $this->buildPayload($toLog);
        $payload = $this->config->transform($payload, $this->level, $toLog, $this->context);
        $custom = $payload->getData()->getCustom();

        $this->assertEquals([
            'message' => $toLog->getMessage(),
            'code' => $toLog->getCode(),
            'file' => $toLog->getFile(),
            'line' => $toLog->getLine(),
            'dynamicProperty' => $toLog->dynamicProperty,
        ], $custom['properties']);
    }

    public function test_unencodable_types_are_replaced_with_null()
    {
        $toLog = new Exception('Test log message');
        $toLog->scalarString = 'scalar string';
        $toLog->scalarInt = 10;
        $toLog->scalarFloat = 10.10;
        $toLog->scalarBool = true;
        $toLog->scalarNull = null;
        $toLog->numericArray = ['first', 'second'];
        $toLog->assocArray = ['one' => 'first', 'two' => 'second'];
        $toLog->nestedArray = ['one' => ['one' => 'first', 'two' => 'second'], 'two' => 'second'];
        $toLog->simpleClass = (object)['one' => 'first', 'two' => 'second'];
        $toLog->nestedClass = (object)['one' => (object)['one' => 'first', 'two' => 'second', 'invalid' => opendir('.')], 'two' => 'second'];
        $toLog->invalidResource = opendir('.');

        $payload = $this->buildPayload($toLog);
        $payload = $this->config->transform($payload, $this->level, $toLog, $this->context);
        $custom = $payload->getData()->getCustom();

        $this->assertEquals([
            'message' => $toLog->getMessage(),
            'code' => $toLog->getCode(),
            'file' => $toLog->getFile(),
            'line' => $toLog->getLine(),
            'scalarString' => $toLog->scalarString,
            'scalarInt' => $toLog->scalarInt,
            'scalarFloat' => $toLog->scalarFloat,
            'scalarBool' => $toLog->scalarBool,
            'scalarNull' => $toLog->scalarNull,
            'numericArray' => $toLog->numericArray,
            'assocArray' => $toLog->assocArray,
            'nestedArray' => $toLog->nestedArray,
            'simpleClass' => ['one' => 'first', 'two' => 'second'],
            'nestedClass' => ['one' => ['one' => 'first', 'two' => 'second', 'invalid' => null], 'two' => 'second'],
            'invalidResource' => null,
        ], $custom['properties']);
    }

    public function test_exception_chain_is_included()
    {
        $toLog = new Exception('First log message', 1, new Exception('Second log message', 2, new TestPropertiesTransformerException('Third log message', 3)));

        $payload = $this->buildPayload($toLog);
        $payload = $this->config->transform($payload, $this->level, $toLog, $this->context);
        $custom = $payload->getData()->getCustom();

        $properties = $custom['properties'];

        /** @var Exception */
        $second = $toLog->getPrevious();
        /** @var \ShiftOneLabs\PhpRollbarExtensions\Tests\Fakes\TestPropertiesTransformerException */
        $third = $second->getPrevious();

        $this->assertEquals([
            'message' => $toLog->getMessage(),
            'code' => $toLog->getCode(),
            'file' => $toLog->getFile(),
            'line' => $toLog->getLine(),
            'previous' => [
                'Exception' => [
                    'message' => $second->getMessage(),
                    'code' => $second->getCode(),
                    'file' => $second->getFile(),
                    'line' => $second->getLine(),
                    'previous' => [
                        'ShiftOneLabs\PhpRollbarExtensions\Tests\Fakes\TestPropertiesTransformerException' => array_merge(
                            [
                                'message' => $third->getMessage(),
                                'code' => $third->getCode(),
                                'file' => $third->getFile(),
                                'line' => $third->getLine(),
                            ],
                            $third->toArray()
                        )
                    ],
                ]
            ],
        ], $properties);
    }

    protected function buildPayload($toLog)
    {
        $data = $this->config->getRollbarData($this->level, $toLog, $this->context);

        return new Payload($data, $this->config->getAccessToken());
    }
}
