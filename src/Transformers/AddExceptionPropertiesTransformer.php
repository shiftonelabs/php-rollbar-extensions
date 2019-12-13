<?php

namespace ShiftOneLabs\PhpRollbarExtensions\Transformers;

use Error;
use Exception;
use Throwable;
use ReflectionClass;
use Rollbar\Payload\Payload;
use Rollbar\TransformerInterface;

class AddExceptionPropertiesTransformer implements TransformerInterface
{
    /**
     * Transform the payload.
     *
     * This transformer modifies the payload to add all the values of all
     * the properties of all the exceptions in the exception chain to
     * a properties key on the data custom element.
     *
     * @param  \Rollbar\Payload\Payload  $payload
     * @param  \Rollbar\Payload\Level  $level
     * @param  \Exception|\Throwable  $toLog
     * @param  $context
     *
     * @return \Rollbar\Payload\Payload
     */
    public function transform(Payload $payload, $level, $toLog, $context)
    {
        // The interface docblock only specifies Exceptions and Throwables, but
        // $toLog can also be a string. Make sure we only work on Throwables.
        if (!$toLog instanceof Throwable) {
            return $payload;
        }

        $custom = $payload->getData()->getCustom();

        $custom['properties'] = json_decode(json_encode($this->getPropertyValues($toLog), JSON_PARTIAL_OUTPUT_ON_ERROR), true);

        $payload->getData()->setCustom($custom);

        return $payload;
    }

    /**
     * Recursively get the property values for the exception chain.
     *
     * @param  \Throwable  $exception
     *
     * @return array
     */
    protected function getPropertyValues(Throwable $exception)
    {
        // Get all the values of all the object's properties. Use reflection
        // to access all the defined properties, and use get_object_vars
        // to access all the dynamically added properties.
        $properties = array_merge(
            array_reduce(
                (new ReflectionClass($exception))->getProperties(),
                function ($carry, $property) use ($exception) {
                    // Exclude some unneeded private properties from the base
                    // Exception and Error classes.
                    if ($property->isPrivate()
                        && in_array(get_class($exception), [Exception::class, Error::class])
                        && in_array($property->getName(), ['string', 'trace', 'previous'])) {
                        return $carry;
                    }

                    $property->setAccessible(true);

                    $carry[$property->getName()] = $property->getValue($exception);

                    return $carry;
                },
                []
            ),
            get_object_vars($exception)
        );

        // Go down the exception chain to get all the nested exception values.
        if (($previous = $exception->getPrevious()) instanceof Throwable) {
            $properties['previous'] = [get_class($previous) => $this->getPropertyValues($previous)];
        }

        return $properties;
    }
}
