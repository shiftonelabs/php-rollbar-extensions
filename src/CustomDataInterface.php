<?php

namespace ShiftOneLabs\PhpRollbarExtensions;

interface CustomDataInterface
{
    /**
     * The __invoke method is called when a script tries to call an object as
     * a function.
     *
     * The Rollbar custom_data_method option expects a callable. An object
     * instance that implements the __invoke method can be treated as
     * a callable.
     *
     * @param  \Exception|\Throwable|mixed  $toLog
     * @param  $context
     *
     * @return array
     */
    public function __invoke($toLog, $context);
}
