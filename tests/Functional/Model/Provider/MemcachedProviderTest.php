<?php

/**
 * LICENSE: [EMAILBIDDING_DESCRIPTION_LICENSE_HERE]
 *
 * @author     Diogo Teixeira <diogo.teixeira@emailbidding.com>
 * @copyright  2012-2015 Emailbidding
 * @license    [EMAILBIDDING_URL_LICENSE_HERE]
 */

namespace EBT\CacheClient\Tests\Functional\Model\Provider;

use EBT\CacheClient\Model\Provider\MemcachedProvider;
use EBT\CacheClient\Model\ProviderInterface;
use EBT\CacheClient\Service\Factory\ProviderFactoryService;

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
class MemcachedProviderTest extends BaseProviderTest
{
    /**
     * @var \Memcached
     */
    protected $client;

    /**
     * @var ProviderInterface
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
            ProviderInterface::PROVIDER_OPT_PREFIX    => 'my_prefix',
            ProviderInterface::PROVIDER_OPT_SEPARATOR => ':',
        );
        $this->provider = (new ProviderFactoryService())->getMemcached($this->client, $options);
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
