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

use EBT\CacheClient\Entity\CacheResponse;

class MemcachedProvider extends BaseProvider
{
    /**
     * @const string
     */
    const PROVIDER_NAME = 'Memcached';

    /**
     * @var \Memcached
     */
    protected $client;

    /**
     * Constructor.
     *
     * @param \Memcached $client Client instance.
     */
    public function __construct(\Memcached $client)
    {
        parent::__construct();

        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    protected function doGet($key, array $options = array())
    {
        $result = $this->client->get($this->getKey($key, $options));

        switch (true) {
            case $this->isSuccess():
                return new CacheResponse($result, true, true);
            case $this->isNotFound():
                return new CacheResponse(false, false, true, CacheResponse::RESOURCE_NOT_FOUND);
        }

        /* If everything failed we're dealing with a backend (connection) error. */
        return new CacheResponse(false, false, false, CacheResponse::CONNECTION_ERROR);
    }

    /**
     * {@inheritdoc}
     */
    protected function doSet($key, $value, $expiration, array $options = array())
    {
        $this->client->set($this->getKey($key, $options), $value, $expiration);

        /* Success should never fail, so anything other than success is also a server (connection) error. */
        if ($this->isSuccess()) {
            return new CacheResponse(true, true, true);
        }

        return new CacheResponse(false, false, false, CacheResponse::CONNECTION_ERROR);
    }

    /**
     * {@inheritdoc}
     */
    protected function doDelete($key, array $options = array())
    {
        $this->client->delete($this->getKey($key, $options));

        switch (true) {
            case $this->isSuccess():
                return new CacheResponse(true, true, true);
            case $this->isNotFound():
                return new CacheResponse(false, false, true, CacheResponse::RESOURCE_NOT_FOUND);
        }

        /* If everything failed we're dealing with a backend (connection) error. */
        return new CacheResponse(false, false, false, CacheResponse::CONNECTION_ERROR);
    }

    /**
     * {@inheritdoc}
     */
    protected function doFlush($namespace)
    {
        return $this->doDelete($namespace);
    }

    /**
     * {@inheritdoc}
     */
    protected function getProviderName()
    {
        return self::PROVIDER_NAME;
    }

    /**
     * Retrieves the result code of the last client call.
     *
     * @return int
     */
    protected function getResultCode()
    {
        return $this->getClient()->getResultCode();
    }

    /**
     * Checks if the last operation returned a resource not found code.
     *
     * @return boolean
     */
    protected function isNotFound()
    {
        return \Memcached::RES_NOTFOUND === $this->getResultCode();
    }

    /**
     * Checks if the last operation returned a resource not stored code.
     *
     * @return boolean
     */
    protected function isNotStored()
    {
        return \Memcached::RES_NOTSTORED === $this->getResultCode();
    }

    /**
     * Checks if the last operation returned a success code.
     *
     * @return boolean
     */
    protected function isSuccess()
    {
        return \Memcached::RES_SUCCESS === $this->getResultCode();
    }

    /**
     * Checks if Memcached optional binary protocol is active.
     *
     * @return boolean
     */
    protected function isBinaryProtocolActive()
    {
        return (bool) $this->getClient()->getOption(\Memcached::OPT_BINARY_PROTOCOL);
    }

    /**
     * Retrieves the Memcached client instance.
     *
     * @return \Memcached
     */
    protected function getClient()
    {
        return $this->client;
    }
}
