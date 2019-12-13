<?php

namespace ShiftOneLabs\PhpRollbarExtensions\Tests;

use Rollbar\Rollbar;
use ReflectionProperty;
use PHPUnit_Framework_TestCase;

class TestCase extends PHPUnit_Framework_TestCase
{
    const DEFAULT_ACCESS_TOKEN = 'ad865e76e7fb496fab096ac07b1dbabb';

    protected $env = 'rollbar-php-testing';

    public function tearDown()
    {
        $this->clearLogger();

        parent::tearDown();
    }

    public function getTestAccessToken()
    {
        return isset($_ENV['ROLLBAR_TEST_TOKEN']) ? $_ENV['ROLLBAR_TEST_TOKEN'] : static::DEFAULT_ACCESS_TOKEN;
    }

    protected function clearLogger()
    {
        // Rollbar::destroy() wasn't introduced until 1.4.1.
        if (method_exists(Rollbar::class, 'destroy')) {
            return Rollbar::destroy();
        }

        // Use reflection to clear the logger for 1.2.0 - 1.4.0.
        $loggerProperty = new ReflectionProperty(Rollbar::class, 'logger');
        $loggerProperty->setAccessible(true);
        $loggerProperty->setValue(null);
    }
}
