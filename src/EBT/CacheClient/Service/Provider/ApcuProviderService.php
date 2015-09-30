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

class ApcuProviderService extends BaseProviderService
{
    const PROVIDER_NAME = 'Apcu';

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
        $result = apc_fetch($key, $fetchSuccess);
        if (! $fetchSuccess) {

            return new CacheResponse(false, false, true, CacheResponse::RESOURCE_NOT_FOUND);
        }

        return new CacheResponse($result, true, true);
    }

    /**
     * {@inheritDoc}
     */
    protected function doSet($key, $value, $expiration, array $options = array())
    {
        $result = apc_store($key, $value, $expiration);

        return new CacheResponse($result, $result, true);
    }

    /**
     * {@inheritDoc}
     */
    protected function doDelete($key, array $options = array())
    {
        $result = apc_delete($key);

        return new CacheResponse($result, $result, true);
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
