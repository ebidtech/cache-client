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

use EBT\CacheClient\Entity\CacheResponse;

class MemcachedProviderService extends BaseProviderService
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
     * @param \Memcached $client  Client instance.
     * @param array      $options Provider options.
     */
    public function __construct(\Memcached $client, array $options = array())
    {
        parent::__construct();
        $this->client = $client;

        /* Set the provider options. */
        $this->setOptions($options);
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
    public function doIncrement($key, $increment = 1, $initialValue = 0, $expiration = null, array $options = array())
    {
        $key = $this->getKey($key, $options);
        /* Only the binary protocol supports initial value, in which case the implementation is simpler. */
        if ($this->isBinaryProtocolActive()) {
            $result = $this->client->increment($key, $increment, $initialValue, $expiration);

            return new CacheResponse($result, true, true);
        }

        /* If the binary protocol is disable we must implement the initial value logic. */
        $result = $this->client->increment($key, $increment);

        /* In case or success or any error aside "not found" there's nothing more to do. */
        if ($this->isSuccess() || ! $this->isNotFound()) {

            return new CacheResponse($result, true, true);
        }

        /**
         * Try to add the key; notice that "add" is used instead of "set", to ensure we do not override
         * the value in case another process already set it.
         */
        $result = $this->client->add($key, $increment + $initialValue, $expiration);

        /* Created the key successfully. */
        if ($this->isSuccess()) {

            return new CacheResponse($increment + $initialValue, true, true);
        }

        /* The key was not stored because is already existed, try to increment a last time. */
        if ($this->isNotStored()) {
            $result = $this->client->increment($key, $increment);

            return new CacheResponse($result, true, true);
        }

        return new CacheResponse($result, false, false);
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
        return $this->getClient()
            ->getResultCode();
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
        return (bool) $this->getClient()
            ->getOption(\Memcached::OPT_BINARY_PROTOCOL);
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
