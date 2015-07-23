<?php

/**
 * This file is a part of the Cache Client library.
 *
 * (c) 2015 Ebidtech
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EBT\CacheClient\Tests\Unit\Service\Provider;

use EBT\CacheClient\Entity\CacheResponse;
use EBT\CacheClient\Service\Provider\BaseProviderService;
use EBT\CacheClient\Service\ProviderServiceInterface;
use EBT\CacheClient\Tests\BaseUnitTestCase;

/**
 * @group unit
 * @group unit-provider
 */
class BaseProviderServiceTest extends BaseUnitTestCase
{
    /**
     * @var BaseProviderService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $baseProvider;

    /**
     * @var \ReflectionMethod
     */
    protected $setOptionsMethod;

    /**
     * Tests if the provider options are correctly set.
     */
    public function testSetProviderOptions()
    {
        /* Get target properties using reflection. */
        $prefix    = $this->getPropertyUsingReflection($this->baseProvider, 'prefix');
        $separator = $this->getPropertyUsingReflection($this->baseProvider, 'separator');

        /* Both are not defined. */
        $this->setOptionsMethod->invoke($this->baseProvider, array());
        $this->assertEquals('', $prefix->getValue($this->baseProvider));
        $this->assertEquals('', $separator->getValue($this->baseProvider));

        /* Prefix is empty/null, but separator is not. */
        $this->setOptionsMethod->invoke(
            $this->baseProvider,
            array(
                ProviderServiceInterface::PROVIDER_OPT_PREFIX    => '',
                ProviderServiceInterface::PROVIDER_OPT_SEPARATOR => ':',
            )
        );
        $this->assertEquals('', $prefix->getValue($this->baseProvider));
        $this->assertEquals(':', $separator->getValue($this->baseProvider));

        /* Prefix has value but separator not. */
        $this->setOptionsMethod->invoke(
            $this->baseProvider,
            array(
                ProviderServiceInterface::PROVIDER_OPT_PREFIX    => 'prefix',
                ProviderServiceInterface::PROVIDER_OPT_SEPARATOR => '',
            )
        );
        $this->assertEquals('prefix', $prefix->getValue($this->baseProvider));
        $this->assertEquals('', $separator->getValue($this->baseProvider));

        /* Both have value. */
        $this->setOptionsMethod->invoke(
            $this->baseProvider,
            array(
                ProviderServiceInterface::PROVIDER_OPT_PREFIX    => 'prefix',
                ProviderServiceInterface::PROVIDER_OPT_SEPARATOR => ':',
            )
        );
        $this->assertEquals('prefix:', $prefix->getValue($this->baseProvider));
        $this->assertEquals(':', $separator->getValue($this->baseProvider));
    }

    /**
     * Tests getting a key without a namespace.
     */
    public function testGetKeyNoNamespace()
    {
        $method = $this->getMethodUsingReflection($this->baseProvider, 'getKey');
        $this->assertEquals('prefix:my_key', $method->invoke($this->baseProvider, 'my_key', array()));
    }

    /**
     * Tests getting a key from an existing namespace.
     */
    public function testGetKeyExistingNamespace()
    {
        /* Setup method calls. */
        $method = $this->getMethodUsingReflection($this->baseProvider, 'getKey');
        $this->baseProvider
            ->expects($this->once())
            ->method('doGet')
            ->with('my_ns')
            ->will($this->returnValue(new CacheResponse('12345', true, true)));
        $this->baseProvider
            ->expects($this->never())
            ->method('doSet');

        /* Call method. */
        $options = array(
            ProviderServiceInterface::CMD_OPT_NAMESPACE => 'my_ns',
        );
        $this->assertEquals(
            'prefix:my_ns:12345:my_key',
            $method->invoke($this->baseProvider, 'my_key', $options)
        );
    }

    /**
     * Tests getting a key and creating a new namespace for it.
     */
    public function testGetKeyCreateNamespace()
    {
        /* Setup method calls. */
        $method = $this->getMethodUsingReflection($this->baseProvider, 'getKey');
        $this->baseProvider
            ->expects($this->once())
            ->method('doGet')
            ->with('my_ns')
            ->will($this->returnValue(new CacheResponse(false, false, false, 'Some error')));
        $this->baseProvider
            ->expects($this->once())
            ->method('doSet')
            ->with('my_ns', $this->matchesRegularExpression('/^[1-9][0-9]*$/'), 30)
            ->will($this->returnValue(true));

        /* Call method. */
        $options = array(
            ProviderServiceInterface::CMD_OPT_NAMESPACE            => 'my_ns',
            ProviderServiceInterface::CMD_OPT_NAMESPACE_EXPIRATION => 30,
        );
        $this->assertRegExp(
            '/^prefix:my_ns:[1-9][0-9]*:my_key$/',
            $method->invoke($this->baseProvider, 'my_key', $options)
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        /* Create a mock for the abstract class. */
        $this->baseProvider = $this->getMockForAbstractClass('EBT\CacheClient\Service\Provider\BaseProviderService');

        $this->setOptionsMethod = $this->getMethodUsingReflection($this->baseProvider, 'setOptions');
        $this->setOptionsMethod->setAccessible(true);
        $options = array(
            ProviderServiceInterface::PROVIDER_OPT_PREFIX    => 'prefix',
            ProviderServiceInterface::PROVIDER_OPT_SEPARATOR => ':',
        );
        $this->setOptionsMethod->invoke($this->baseProvider, $options);
    }
}
