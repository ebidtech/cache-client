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
        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    public function get($key, array $options = array())
    {
        $result = $this->client->get($this->getKey($key, $options));

        return new CacheResponse($result, $this->isSuccess());
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value, $expiration = null, array $options = array())
    {
        $result = $this->client->set($this->getKey($key, $options), $value, $expiration);

        return new CacheResponse($result, $this->isSuccess());
    }

    /**
     * {@inheritdoc}
     */
    public function increment($key, $increment = 1, $initialValue = 0, $expiration = null, array $options = array())
    {
        $key = $this->getKey($key, $options);

        /* Only the binary protocol supports initial value, in which case the implementation is simpler. */
        if ($this->isBinaryProtocolActive()) {
            $result = $this->client->increment($key, $increment, $initialValue, $expiration);

            return new CacheResponse($result, $this->isSuccess());
        }

        /* If the binary protocol is disable we must implement the initial value logic. */
        $result = $this->client->increment($key, $increment);

        /* In case or success or any error aside "not found" there's nothing more to do. */
        if ($this->isSuccess() || ! $this->isNotFound()) {

            return new CacheResponse($result, $this->isSuccess());
        }

        /* Try to add the key; notice that "add" is used instead of "set", to ensure we do not override
         * the value in case another process already set it.
         */
        $result = $this->client->add($key, $increment + $initialValue, $expiration);

        /* Created the key successfully. */
        if ($this->isSuccess()) {

            return new CacheResponse($increment + $initialValue, $this->isSuccess());
        }

        /* The key was not stored because is already existed, try to increment a last time. */
        if ($this->isNotStored()) {
            $result = $this->client->increment($key, $increment);

            return new CacheResponse($result, $this->isSuccess());
        }

        return new CacheResponse($result, $this->isSuccess());
    }

    /**
     * {@inheritdoc}
     */
    public function lock($key, $owner = null, $expiration = null, array $options = array())
    {
        $result = $this->client->add($this->getKey($key, $options), $owner, $expiration);

        return new CacheResponse($result, $this->isSuccess() || $this->isNotStored());
    }

    /**
     * {@inheritdoc}
     */
    public function lockExists($key, array $options = array())
    {
        $result = $this->client->get($this->getKey($key, $options));

        /* The locks exists, return true. */
        if ($this->isSuccess()) {

            return new CacheResponse(true, $this->isSuccess());
        }

        /* The locks does not exist, return false (still success). */
        if ($this->isNotFound()) {

            return new CacheResponse(false, $this->isNotFound());
        }

        /* Another error occurred, just return failure and the call result. */
        return new CacheResponse($result, $this->isSuccess());
    }

    /**
     * {@inheritdoc}
     */
    public function delete($key, array $options = array())
    {
        $result = $this->client->delete($this->getKey($key, $options));

        return new CacheResponse($result, $this->isSuccess());
    }

    /**
     * {@inheritdoc}
     */
    public function flush($namespace)
    {
        return $this->delete($namespace);
    }

    /**
     * Checks if the last operation returned a resource not found code.
     *
     * @return boolean
     */
    protected function isNotFound()
    {
        return \Memcached::RES_NOTFOUND === $this->client->getResultCode();
    }

    /**
     * Checks if the last operation returned a resource not stored code.
     *
     * @return boolean
     */
    protected function isNotStored()
    {
        return \Memcached::RES_NOTSTORED === $this->client->getResultCode();
    }

    /**
     * Checks if the last operation returned a success code.
     *
     * @return boolean
     */
    protected function isSuccess()
    {
        return \Memcached::RES_SUCCESS === $this->client->getResultCode();
    }

    /**
     * Checks if Memcached optional binary protocol is active.
     *
     * @return boolean
     */
    protected function isBinaryProtocolActive()
    {
        return (bool) $this->client->getOption(\Memcached::OPT_BINARY_PROTOCOL);
    }
}
