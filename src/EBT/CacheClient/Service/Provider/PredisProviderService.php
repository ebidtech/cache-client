<?php

/**
 * This file is a part of the Cache Client library.
 *
 * (c) 2015 Ebidtech
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EBT\CacheClient\Service\Provider;

use Predis\ClientInterface;

class PredisProviderService extends BaseProviderService
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
     * @param ClientInterface $client  Predis client instance.
     * @param array           $options Provider options.
     */
    public function __construct(ClientInterface $client, array $options = array())
    {
        parent::__construct();

        $this->client = $client;

        /* Set the provider options. */
        $this->setOptions($options);
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
     * {@inheritDoc}
     */
    protected function doGet($key, array $options = array())
    {
        // TODO: Implement doGet() method.
        throw new \Exception('Method not implemented');
    }

    /**
     * {@inheritDoc}
     */
    protected function doSet($key, $value, $expiration, array $options = array())
    {
        // TODO: Implement doSet() method.
        throw new \Exception('Method not implemented');
    }

    /**
     * {@inheritDoc}
     */
    protected function doDelete($key, array $options = array())
    {
        // TODO: Implement doDelete() method.
        throw new \Exception('Method not implemented');
    }

    /**
     * {@inheritDoc}
     */
    protected function doIncrement(
        $key,
        $increment = 1,
        $initialValue = 0,
        $expiration = null,
        array $options = array()
    ) {
        // TODO: Implement doIncrement() method.
        throw new \Exception('Method not implemented');
    }

    /**
     * {@inheritDoc}
     */
    protected function doFlush($namespace)
    {
        // TODO: Implement doFlush() method.
        throw new \Exception('Method not implemented');
    }
}
