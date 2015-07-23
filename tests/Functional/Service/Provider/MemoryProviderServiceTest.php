<?php

/**
 * LICENSE: [EMAILBIDDING_DESCRIPTION_LICENSE_HERE]
 *
 * @author     Diogo Teixeira <diogo.teixeira@emailbidding.com>
 * @copyright  2012-2015 Emailbidding
 * @license    [EMAILBIDDING_URL_LICENSE_HERE]
 */

namespace EBT\CacheClient\Tests\Functional\Service\Provider;

use EBT\CacheClient\Service\Provider\MemoryProviderService;
use EBT\CacheClient\Service\ProviderServiceInterface;

/**
 * @group functional
 * @group functional-provider
 */
class MemoryProviderServiceTest extends BaseProviderServiceTest
{
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

        /* Create the provider instance. */
        $options = array(
            ProviderServiceInterface::PROVIDER_OPT_PREFIX    => 'my_prefix',
            ProviderServiceInterface::PROVIDER_OPT_SEPARATOR => ':',
        );
        $this->provider = new MemoryProviderService($options);
    }

    /**
     * @inheritDoc
     */
    protected function getProvider()
    {
        return $this->provider;
    }
}
