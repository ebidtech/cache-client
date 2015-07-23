<?php

/**
 * LICENSE: [EMAILBIDDING_DESCRIPTION_LICENSE_HERE]
 *
 * @author     Diogo Teixeira <diogo.teixeira@emailbidding.com>
 * @copyright  2012-2015 Emailbidding
 * @license    [EMAILBIDDING_URL_LICENSE_HERE]
 */

namespace EBT\CacheClient\Tests\Functional\Service\Provider;

use EBT\CacheClient\Service\ProviderServiceInterface;
use EBT\CacheClient\Tests\BaseFunctionalTestCase;

abstract class BaseProviderServiceTest extends BaseFunctionalTestCase
{
    /**
     * Tests if a key can be successfully retrieved from the cache with a simple key.
     */
    public function testSetGetSimpleKeySuccess()
    {
        $this->getProvider()->set('my_first_key', 'my_first_value');
        $result = $this->getProvider()->get('my_first_key');

        $this->assertTrue($result->isInstructionSuccessful());
        $this->assertEquals('my_first_value', $result->getResult());
    }

    /**
     * Retrieves the provider being tested.
     *
     * @return ProviderServiceInterface
     */
    abstract protected function getProvider();

    /**
     * Tests if a key can be successfully retrieved from the cache with a namespaced key.
     */
    public function testSetGetNamespacedKeySuccess()
    {
        $options = array(ProviderServiceInterface::CMD_OPT_NAMESPACE => 'my_namespace');
        $this->getProvider()->set('my_second_key', 'my_second_value', null, $options);
        $result    = $this->getProvider()->get('my_second_key', $options);
        $namespace = $this->getProvider()->get('my_namespace');

        $this->assertTrue($result->isInstructionSuccessful());
        $this->assertEquals('my_second_value', $result->getResult());
        $this->assertTrue($namespace->isInstructionSuccessful());
        $this->assertRegExp('/[1-9][0-9]*/', $namespace->getResult());
    }

    /**
     * Tests if a key can be successfully retrieved from the cache with an expiration.
     */
    public function testSetGetExpirationSuccess()
    {
        $this->getProvider()->set('my_third_key', 'my_third_value', 30);
        $result = $this->getProvider()->get('my_third_key');

        $this->assertTrue($result->isInstructionSuccessful());
        $this->assertEquals('my_third_value', $result->getResult());
    }

    /**
     * Tests failure to get key when it does not exist.
     */
    public function testSetGetNotFoundFailure()
    {
        $result = $this->getProvider()->get('my_key');
        $this->assertFalse($result->isInstructionSuccessful());
        $this->assertContains('not found', $result->getErrorMessage());
    }

    /**
     * Tests failure to get key when there are invalid parameters.
     */
    public function testSetGetInvalidParametersFailure()
    {
        $result = $this->getProvider()->get('');
        $this->assertContains('string not empty', $result->getErrorMessage());
        $result = $this->getProvider()->get(null);
        $this->assertContains('string not empty', $result->getErrorMessage());
    }

    /**
     * Tests failure to get key when it expires.
     */
    public function testSetGetExpirationFailure()
    {
        $this->getProvider()->set('my_key', 'my_value', 1);

        /* We have to sleep to force key expiration. */
        sleep(1);
        $result = $this->getProvider()->get('my_key');

        $this->assertFalse($result->isInstructionSuccessful());
        $this->assertContains('not found', $result->getErrorMessage());
    }

    /**
     * Tests failure to get key when the namespace expires (but the key does not).
     */
    public function testSetGetNamespaceExpirationFailure()
    {
        /* Namespace expires after 1 second, but the does not. However, the key should not be accessible. */
        $options = array(
            ProviderServiceInterface::CMD_OPT_NAMESPACE            => 'my_namespace',
            ProviderServiceInterface::CMD_OPT_NAMESPACE_EXPIRATION => 1,
        );
        $this->getProvider()->set('my_key', 'my_value', null, $options);

        /* Sleep to let the namespace expire. */
        sleep(1);
        $result = $this->getProvider()->get('my_key', $options);

        $this->assertFalse($result->isInstructionSuccessful());
        $this->assertContains('not found', $result->getErrorMessage());
    }

    /**
     * Tests failure conditions of the delete method.
     */
    public function testDeleteFailure()
    {
        /* Delete should fail where there's no key to delete. */
        $result = $this->getProvider()->delete('my_key');

        $this->assertFalse($result->getResult());
        $this->assertFalse($result->isInstructionSuccessful());
        $this->assertContains('not found', $result->getErrorMessage());
    }

    /**
     * Tests success cases of the delete method.
     */
    public function testDeleteSuccess()
    {
        $this->getProvider()->set('my_key', 'my_value');
        $deleteResult = $this->getProvider()->delete('my_key');
        $getResult    = $this->getProvider()->get('my_key');

        $this->assertTrue($deleteResult->getResult());
        $this->assertFalse($getResult->getResult());
        $this->assertContains('not found', $getResult->getErrorMessage());
    }

    /**
     * Tests failure conditions of the flush method.
     */
    public function testFlushFailure()
    {
        /* Delete should fail where there's no key to delete. */
        $result = $this->getProvider()->flush('my_namespace');

        $this->assertFalse($result->getResult());
        $this->assertFalse($result->isInstructionSuccessful());
        $this->assertContains('not found', $result->getErrorMessage());
    }

    /**
     * Tests success cases of the delete method.
     */
    public function testFlushSuccess()
    {
        $options = array(ProviderServiceInterface::CMD_OPT_NAMESPACE => 'my_namespace');
        $this->getProvider()->set('my_key', 'my_value', null, $options);
        $flushResult = $this->getProvider()->flush('my_namespace');
        $getResult   = $this->getProvider()->get('my_key');

        $this->assertTrue($flushResult->getResult());
        $this->assertFalse($getResult->getResult());
        $this->assertContains('not found', $getResult->getErrorMessage());
    }
}
