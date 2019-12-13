<?php

namespace ShiftOneLabs\PhpRollbarExtensions\Tests;

use Rollbar\Rollbar;
use PHPUnit_Framework_TestCase;

class TestCase extends PHPUnit_Framework_TestCase
{
    const DEFAULT_ACCESS_TOKEN = 'ad865e76e7fb496fab096ac07b1dbabb';

    protected $env = 'rollbar-php-testing';

    public function tearDown()
    {
        Rollbar::destroy();
        parent::tearDown();
    }

    public function getTestAccessToken()
    {
        return isset($_ENV['ROLLBAR_TEST_TOKEN']) ? $_ENV['ROLLBAR_TEST_TOKEN'] : static::DEFAULT_ACCESS_TOKEN;
    }
}
