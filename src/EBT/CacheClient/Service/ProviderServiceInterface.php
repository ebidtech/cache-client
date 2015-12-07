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
     * Please note that different providers might have different ways of storing the given values, possibly applying
     * transformations to those values. As such, you should not use these providers in together third party ones, as
     * value representations might not be compatible between them.
     *
     * If using third party providers is necessary, be sure to check the source code of both, and make the necessary
     * changes to ensure compatibility between any existing value encoding.
     *
     * @param string                $key        Key to set.
     * @param string|int|float|bool $value      Value to set (any scalar value).
     * @param integer|null          $expiration Key TTL.
     * @param array                 $options    Additional options.
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
     * Deletes all cached keys in a namespace. This operation is not guaranteed to delete the affected
     * keys, it only ensures a "logical delete" (those keys are no longer accessible within the namespace).
     *
     * @param string $namespace
     *
     * @return CacheResponse
     */
    public function flush($namespace);
}
