<?php

/**
 * This file is a part of the Cache Client library.
 *
 * (c) 2015 Ebidtech
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EBT\CacheClient\Model\Provider;

use Predis\ClientInterface;

class PredisProvider extends BaseProvider
{
    /**
     * @const string
     */
    const PROVIDER_NAME = 'Predis';

    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * Constructor.
     *
     * @param ClientInterface $client Predis client instance.
     */
    public function __construct(ClientInterface $client)
    {
        parent::__construct();

        $this->client = $client;
    }

    /**
     * Returns the name of the provider.
     *
     * @return string
     */
    protected function getProviderName()
    {
        return self::PROVIDER_NAME;
    }

    /**
     * @inheritDoc
     */
    protected function doGet($key, array $options = array())
    {
        // TODO: Implement doGet() method.
        throw new \Exception('Method not implemented');
    }

    /**
     * @inheritDoc
     */
    protected function doSet($key, $value, $expiration, array $options = array())
    {
        // TODO: Implement doSet() method.
        throw new \Exception('Method not implemented');
    }

    /**
     * @inheritDoc
     */
    protected function doDelete($key, array $options = array())
    {
        // TODO: Implement doDelete() method.
        throw new \Exception('Method not implemented');
    }

    /**
     * @inheritDoc
     */
    protected function doFlush($namespace)
    {
        // TODO: Implement doFlush() method.
        throw new \Exception('Method not implemented');
    }
}
