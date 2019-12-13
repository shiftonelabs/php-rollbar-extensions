<?php

namespace ShiftOneLabs\PhpRollbarExtensions\Tests\Fakes;

use Exception;

class TestPropertiesTransformerException extends Exception
{
    public $publicVar = 'public var';
    protected $protectedVar = 'protected var';
    private $privateVar = 'private var';
    static public $staticPublicVar = 'static public var';
    static protected $staticProtectedVar = 'static protected var';
    static private $staticPrivateVar = 'static private var';

    public function toArray()
    {
        return [
            'publicVar' => $this->publicVar,
            'protectedVar' => $this->protectedVar,
            'privateVar' => $this->privateVar,
            'staticPublicVar' => self::$staticPublicVar,
            'staticProtectedVar' => self::$staticProtectedVar,
            'staticPrivateVar' => self::$staticPrivateVar,
        ];
    }
}
