<?php

/**
 * This file is a part of the Cache Client library.
 *
 * (c) 2015 Ebidtech
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EBT\CacheClient\Service\Factory;

use EBT\CacheClient\Exception\InvalidArgumentException;
use EBT\CacheClient\Model\Provider\MemcachedProvider;
use EBT\CacheClient\Model\Provider\MemoryProvider;
use EBT\CacheClient\Model\Provider\PredisProvider;
use EBT\CacheClient\Service\ProviderFactoryServiceInterface;
use Predis\ClientInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProviderFactoryService implements ProviderFactoryServiceInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getPredis(ClientInterface $client, array $options = array())
    {
        /* Instantiate the provider service. */
        $provider = new PredisProvider($client);
        $provider->setProviderOptions($options);

        return $provider;
    }

    /**
     * {@inheritdoc}
     */
    public static function getMemcached(\Memcached $client, array $options = array())
    {
        /* Instantiate the provider service. */
        $provider = new MemcachedProvider($client);
        $provider->setProviderOptions($options);

        return $provider;
    }

    /**
     * {@inheritdoc}
     */
    public static function getMemory(array $options = array())
    {
        /* Instantiate the provider service. */
        $provider = new MemoryProvider();
        $provider->setProviderOptions($options);

        return $provider;
    }
}
