<?php

/**
 * LICENSE: [EMAILBIDDING_DESCRIPTION_LICENSE_HERE]
 *
 * @author     Diogo Teixeira <diogo.teixeira@emailbidding.com>
 * @copyright  2012-2015 Emailbidding
 * @license    [EMAILBIDDING_URL_LICENSE_HERE]
 */

namespace EBT\CacheClient\Tests\Functional\Model\Provider;

use EBT\CacheClient\Model\ProviderInterface;
use EBT\CacheClient\Service\Factory\ProviderFactoryService;

/**
 * @group functional
 * @group functional-provider
 */
class MemoryProviderTest extends BaseProviderTest
{
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

        /* Create the provider instance. */
        $options = array(
            ProviderInterface::PROVIDER_OPT_PREFIX    => 'my_prefix',
            ProviderInterface::PROVIDER_OPT_SEPARATOR => ':',
        );
        $this->provider = (new ProviderFactoryService())->getMemory($options);
    }

    /**
     * @inheritDoc
     */
    protected function getProvider()
    {
        return $this->provider;
    }
}
