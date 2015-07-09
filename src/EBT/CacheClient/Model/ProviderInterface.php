<?php

/**
 * This file is a part of the Cache Client library.
 *
 * (c) 2015 Ebidtech
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EBT\CacheClient\Model;

use EBT\CacheClient\Entity\CacheResponse;

interface ProviderInterface
{
    /* Cache provider options. */
    const PROVIDER_OPT_PREFIX         = 'prefix';
    const PROVIDER_OPT_SEPARATOR      = 'separator';
    const PROVIDER_OPT_GC_PROBABILITY = 'gc_probability';
    const PROVIDER_OPT_GC_DIVISOR     = 'gc_divisor';

    /* Cache command options. */
    const CMD_OPT_NAMESPACE            = 'namespace';
    const CMD_OPT_NAMESPACE_EXPIRATION = 'namespace_expiration';

    /**
     * Fetches the value stored under a given key.
     *
     * @param string $key     The key to fetch.
     * @param array  $options Additional options.
     *
     * @return CacheResponse The stored value when it exists, FALSE on failure.
     */
    public function get($key, array $options = array());

    /**
     * Sets a new value in the cache.
     *
     * @param string       $key        Key to set.
     * @param mixed        $value      Value to set (note that null values will be converted to false).
     * @param integer|null $expiration Key TTL.
     * @param array        $options    Additional options.
     *
     * @return CacheResponse TRUE when the value is set, FALSE on failure.
     */
    public function set($key, $value, $expiration = null, array $options = array());

    /**
     * Increments a numeric value stored under the given key (creates the key if it does not exist).
     *
     * @param string       $key          The key to increment.
     * @param integer      $increment    Value to increment.
     * @param integer      $initialValue Initial value to set when the key does not exist.
     * @param integer|null $expiration   Key TTL (only applies when the key is created).
     * @param array        $options      Additional options.
     *
     * @return CacheResponse The new value when it is incremented, FALSE on failure.
     */
    public function increment($key, $increment = 1, $initialValue = 0, $expiration = null, array $options = array());

    /**
     * Locks a key.
     *
     * @param string       $key        Key to be locked.
     * @param string|null  $owner      Owner of the key, if null the key is saved with value TRUE.
     * @param integer|null $expiration Key TTL (only applies when the key is created).
     * @param array        $options    Additional options.
     *
     * @return CacheResponse TRUE if the lock was acquired, FALSE if it was not and FALSE on failure.
     */
    public function lock($key, $owner = null, $expiration = null, array $options = array());

    /**
     * Checks if a lock exists. This method does not set or release any locks.
     *
     * @param string $key     Key to be checked.
     * @param array  $options Additional options.
     *
     * @return CacheResponse TRUE if the lock was exists, FALSE if it does not and FALSE on failure.
     */
    public function lockExists($key, array $options = array());

    /**
     * Deletes a single key.
     *
     * @param string $key     Key to delete.
     * @param array  $options Additional options.
     *
     * @return CacheResponse TRUE if the key was deleted, FALSE on failure.
     */
    public function delete($key, array $options = array());

    /**
     * Deletes all cached keys in a namespace. This operation is not guaranteed to delete the affected
     * keys, it only ensures a "logical delete" (those keys are no longer accessible within the namespace).
     *
     * @param string $namespace
     *
     * @return CacheResponse TRUE if the namespace was flushed, FALSE on failure.
     */
    public function flush($namespace);
}
