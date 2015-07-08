<?php

/**
 * This file is a part of the Cache Client library.
 *
 * (c) 2015 Ebidtech
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EBT\CacheClient\Tests\Unit;

class BaseUnitTestCase extends \PHPUnit_Framework_TestCase
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
