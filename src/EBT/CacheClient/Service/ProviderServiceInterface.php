<?php

/**
 * This file is a part of the Cache Client library.
 *
 * (c) 2015 Ebidtech
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EBT\CacheClient\Service;

use EBT\CacheClient\Entity\CacheResponse;

/**
 * Interface EBT\CacheClient\Service\ProviderServiceInterface
 *
 * Defines methods that all cache providers should implement. The default response on failure
 * should always be false.
 */
interface ProviderServiceInterface
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
     * @return CacheResponse
     */
    public function get($key, array $options = array());

    /**
     * Sets a new value in the cache.
     *
     * @param string       $key        Key to set.
     * @param string       $value      Value to set.
     * @param integer|null $expiration Key TTL.
     * @param array        $options    Additional options.
     *
     * @return CacheResponse
     */
    public function set($key, $value, $expiration = null, array $options = array());

    /**
     * Deletes a single key.
     *
     * @param string $key     Key to delete.
     * @param array  $options Additional options.
     *
     * @return CacheResponse
     */
    public function delete($key, array $options = array());

    /**
     * Deletes all cached keys in a namespace. This operation is not guaranteed to delete the affected
     * keys, it only ensures a "logical delete" (those keys are no longer accessible within the namespace).
     *
     * @param string $namespace
     *
     * @return CacheResponse
     */
    public function flush($namespace);
}
