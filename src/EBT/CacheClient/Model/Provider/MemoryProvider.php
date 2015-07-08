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

class MemoryProvider extends BaseProvider
{
    /**
     * {@inheritdoc}
     */
    public function get($key, array $options = array())
    {
        // TODO: Implement get() method.
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value, $expiration = null, array $options = array())
    {
        // TODO: Implement set() method.
    }

    /**
     * {@inheritdoc}
     */
    public function increment($key, $increment = 1, $initialValue = 0, $expiration = null, array $options = array())
    {
        // TODO: Implement increment() method.
    }

    /**
     * {@inheritdoc}
     */
    public function lock($key, $owner = null, $expiration = null, array $options = array())
    {
        // TODO: Implement lock() method.
    }

    /**
     * {@inheritdoc}
     */
    public function lockExists($key, array $options = array())
    {
        // TODO: Implement lockExists() method.
    }

    /**
     * {@inheritdoc}
     */
    public function delete($key, array $options = array())
    {
        // TODO: Implement delete() method.
    }

    /**
     * {@inheritdoc}
     */
    public function flush($namespace)
    {
        // TODO: Implement flush() method.
    }
}
