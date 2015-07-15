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
     * {@inheritdoc}
     */
    public function get($key, array $options = array())
    {
        // TODO: Implement get() method.
        throw new \Exception('Method not implemented');
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value, $expiration = null, array $options = array())
    {
        // TODO: Implement set() method.
        throw new \Exception('Method not implemented');
    }

    /**
     * {@inheritdoc}
     */
    public function increment($key, $increment = 1, $initialValue = 0, $expiration = null, array $options = array())
    {
        // TODO: Implement increment() method.
        throw new \Exception('Method not implemented');
    }

    /**
     * {@inheritdoc}
     */
    public function lock($key, $owner = null, $expiration = null, array $options = array())
    {
        // TODO: Implement lock() method.
        throw new \Exception('Method not implemented');
    }

    /**
     * {@inheritdoc}
     */
    public function lockExists($key, array $options = array())
    {
        // TODO: Implement lockExists() method.
        throw new \Exception('Method not implemented');
    }

    /**
     * {@inheritdoc}
     */
    public function delete($key, array $options = array())
    {
        // TODO: Implement delete() method.
        throw new \Exception('Method not implemented');
    }

    /**
     * {@inheritdoc}
     */
    public function flush($namespace)
    {
        // TODO: Implement flush() method.
        throw new \Exception('Method not implemented');
    }
}
