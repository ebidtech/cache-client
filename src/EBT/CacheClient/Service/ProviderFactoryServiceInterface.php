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

use Predis\ClientInterface;

interface ProviderFactoryServiceInterface
{
    /**
     * Creates a new Redis backed cache provider service.
     *
     * @param ClientInterface $client  Predis client.
     * @param array           $options Additional options.
     *
     * @return ProviderServiceInterface
     */
    public static function getPredis(ClientInterface $client, array $options = array());

    /**
     * Creates a new Memcached backed cache provider service.
     *
     * @param \Memcached $client  Memcached client.
     * @param array      $options Additional options.
     *
     * @return ProviderServiceInterface
     */
    public static function getMemcached(\Memcached $client, array $options = array());

    /**
     * Creates a new memory backed cache provider service.
     *
     * @param array $options Additional options.
     *
     * @return ProviderServiceInterface
     */
    public static function getMemory(array $options = array());
}
