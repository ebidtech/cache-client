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

        /* Validate client options. */
        $optionsResolver = new OptionsResolver();
        $provider->configureProviderOptions($optionsResolver);
        $options = self::resolveOptions($optionsResolver, $options, 'Predis');
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

        /* Validate client options. */
        $optionsResolver = new OptionsResolver();
        $provider->configureProviderOptions($optionsResolver);
        $options = self::resolveOptions($optionsResolver, $options, 'Memcached');
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

        /* Validate client options. */
        $optionsResolver = new OptionsResolver();
        $provider->configureProviderOptions($optionsResolver);
        $options = self::resolveOptions($optionsResolver, $options, 'Memory');
        $provider->setProviderOptions($options);

        return $provider;
    }

    /**
     * Resolves and validates a set of options.
     *
     * @param OptionsResolver $optionsResolver
     * @param array           $options
     * @param string          $providerName
     *
     * @returns array An array containing the resolved and validated options.
     */
    protected static function resolveOptions(OptionsResolver $optionsResolver, array $options, $providerName)
    {
        try {
            $options = $optionsResolver->resolve($options);
        } catch (\Exception $e) {
            throw new InvalidArgumentException(
                sprintf(
                    'Invalid configuration for cache provider "%s": %s',
                    $providerName,
                    $e->getMessage()
                )
            );
        }

        return $options;
    }
}
