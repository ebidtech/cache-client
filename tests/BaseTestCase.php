<?php

/**
 * LICENSE: [EMAILBIDDING_DESCRIPTION_LICENSE_HERE]
 *
 * @author     Diogo Teixeira <diogo.teixeira@emailbidding.com>
 * @copyright  2012-2015 Emailbidding
 * @license    [EMAILBIDDING_URL_LICENSE_HERE]
 */

namespace EBT\CacheClient\Tests;

abstract class BaseTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * Allow accessibility to protected/private methods to allow testing them.
     *
     * @param string $class  Class name.
     * @param string $method Method name.
     *
     * @return \ReflectionMethod
     */
    protected function getMethodUsingReflection($class, $method)
    {
        $reflection = new \ReflectionClass($class);
        $method = $reflection->getMethod($method);
        $method->setAccessible(true);

        return $method;
    }

    /**
     * Allow accessibility to protected/private properties to allow testing them.
     *
     * @param string $class    Class name.
     * @param string $property Property name.
     *
     * @return \ReflectionProperty
     */
    protected function getPropertyUsingReflection($class, $property)
    {
        $reflection = new \ReflectionClass($class);
        $property = $reflection->getProperty($property);
        $property->setAccessible(true);

        return $property;
    }
}
