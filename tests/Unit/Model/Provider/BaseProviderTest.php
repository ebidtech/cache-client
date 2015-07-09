<?php

/**
 * This file is a part of the Cache Client library.
 *
 * (c) 2015 Ebidtech
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EBT\CacheClient\Tests\Unit\Model\Provider;

use EBT\CacheClient\Model\Provider\BaseProvider;
use EBT\CacheClient\Model\ProviderInterface;
use EBT\CacheClient\Tests\Unit\BaseUnitTestCase;

/**
 * @group unit
 * @group unit-provider
 */
class BaseProviderTest extends BaseUnitTestCase
{
    /**
     * @var BaseProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $baseProvider;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        /* Create a mock for the abstract class. */
        $this->baseProvider = $this->getMockForAbstractClass('EBT\CacheClient\Model\Provider\BaseProvider');

        /* Setup default provider options. */
        $this->baseProvider->setProviderOptions(
            array(
                ProviderInterface::PROVIDER_OPT_PREFIX    => 'prefix',
                ProviderInterface::PROVIDER_OPT_SEPARATOR => ':'
            )
        );
    }

    /**
     * Tests if the provider options are correctly set.
     */
    public function testSetProviderOptions()
    {
        /* Get target properties using reflection. */
        $prefix = $this->getPropertyUsingReflection($this->baseProvider, 'prefix');
        $separator = $this->getPropertyUsingReflection($this->baseProvider, 'separator');

        /* Both are null. */
        $this->baseProvider->setProviderOptions(
            array(
                ProviderInterface::PROVIDER_OPT_PREFIX    => null,
                ProviderInterface::PROVIDER_OPT_SEPARATOR => null
            )
        );
        $this->assertEquals('', $prefix->getValue($this->baseProvider));
        $this->assertEquals('', $separator->getValue($this->baseProvider));

        /* Prefix is empty/null, but separator is not. */
        $this->baseProvider->setProviderOptions(
            array(
                ProviderInterface::PROVIDER_OPT_PREFIX    => '',
                ProviderInterface::PROVIDER_OPT_SEPARATOR => ':'
            )
        );
        $this->assertEquals('', $prefix->getValue($this->baseProvider));
        $this->assertEquals(':', $separator->getValue($this->baseProvider));

        /* Prefix has value but separator not. */
        $this->baseProvider->setProviderOptions(
            array(
                ProviderInterface::PROVIDER_OPT_PREFIX    => 'prefix',
                ProviderInterface::PROVIDER_OPT_SEPARATOR => ''
            )
        );
        $this->assertEquals('prefix', $prefix->getValue($this->baseProvider));
        $this->assertEquals('', $separator->getValue($this->baseProvider));

        /* Both have value. */
        $this->baseProvider->setProviderOptions(
            array(
                ProviderInterface::PROVIDER_OPT_PREFIX    => 'prefix',
                ProviderInterface::PROVIDER_OPT_SEPARATOR => ':'
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
            ->method('get')
            ->with('prefix:my_ns')
            ->will($this->returnValue('12345'));
        $this->baseProvider
            ->expects($this->never())
            ->method('set');

        /* Call method. */
        $options = array(
            ProviderInterface::CMD_OPT_NAMESPACE => 'my_ns'
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
            ->method('get')
            ->with('prefix:my_ns')
            ->will($this->returnValue(null));
        $this->baseProvider
            ->expects($this->once())
            ->method('set')
            ->with('prefix:my_ns', $this->matchesRegularExpression('/^[1-9][0-9]*$/'), 30)
            ->will($this->returnValue(true));

        /* Call method. */
        $options = array(
            ProviderInterface::CMD_OPT_NAMESPACE            => 'my_ns',
            ProviderInterface::CMD_OPT_NAMESPACE_EXPIRATION => 30
        );
        $this->assertRegExp(
            '/^prefix:my_ns:[1-9][0-9]*:my_key$/',
            $method->invoke($this->baseProvider, 'my_key', $options)
        );
    }
}
