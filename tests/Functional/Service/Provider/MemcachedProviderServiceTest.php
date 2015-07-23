<?php

/**
 * LICENSE: [EMAILBIDDING_DESCRIPTION_LICENSE_HERE]
 *
 * @author     Diogo Teixeira <diogo.teixeira@emailbidding.com>
 * @copyright  2012-2015 Emailbidding
 * @license    [EMAILBIDDING_URL_LICENSE_HERE]
 */

namespace EBT\CacheClient\Tests\Functional\Service\Provider;

use EBT\CacheClient\Service\Provider\MemcachedProviderService;
use EBT\CacheClient\Service\ProviderServiceInterface;

/**
 * This test requires an active memcached server, running on port 11211 of localhost.
 *
 * NEVER RUN THIS TEST ON A PRODUCTION ENVIRONMENT
 *
 * @group functional
 * @group functional-provider
 * @group external
 * @group external-memcached
 */
class MemcachedProviderServiceTest extends BaseProviderServiceTest
{
    /**
     * @var \Memcached
     */
    protected $client;

    /**
     * @var ProviderServiceInterface
     */
    protected $provider;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();

        /* Create the memcached client. */
        $this->client = new \Memcached();
        $this->client->addServer('127.0.0.1', 11211);

        /* Flush the server */
        $this->client->flush();

        /* Create the provider instance. */
        $options = array(
            ProviderServiceInterface::PROVIDER_OPT_PREFIX    => 'my_prefix',
            ProviderServiceInterface::PROVIDER_OPT_SEPARATOR => ':',
        );
        $this->provider = new MemcachedProviderService($this->client, $options);
    }

    /**
     * @inheritDoc
     */
    protected function tearDown()
    {
        parent::tearDown();

        /* Close all active connections. */
        $this->client->quit();
    }

    /**
     * @inheritDoc
     */
    protected function getProvider()
    {
        return $this->provider;
    }
}
