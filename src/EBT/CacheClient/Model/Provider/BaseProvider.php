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

use EBT\CacheClient\Exception\InvalidArgumentException;
use EBT\CacheClient\Model\ProviderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class BaseProvider implements ProviderInterface
{
    /**
     * @var string
     */
    protected $prefix = '';

    /**
     * @var string
     */
    protected $separator = '';

    /**
     * Configures provider specific options.
     *
     * @param OptionsResolver $optionsResolver Options resolver instance.
     *
     * @return OptionsResolver
     */
    public function configureProviderOptions(OptionsResolver $optionsResolver)
    {
        /* Set default values. */
        $optionsResolver->setDefault(ProviderInterface::PROVIDER_OPT_PREFIX, '');
        $optionsResolver->setDefault(ProviderInterface::PROVIDER_OPT_SEPARATOR, '');

        /* Set allowed types. */
        $optionsResolver->setAllowedTypes(ProviderInterface::PROVIDER_OPT_PREFIX, 'string');
        $optionsResolver->setAllowedTypes(ProviderInterface::PROVIDER_OPT_SEPARATOR, 'string');
    }

    /**
     * Sets the default prefix and separator for the provider.
     *
     * @param array $options Provider configuration options.
     */
    public function setProviderOptions(array $options)
    {
        /* Validate options. */
        $optionsResolver = new OptionsResolver();
        $this->configureProviderOptions($optionsResolver);
        $this->resolveOptions($optionsResolver, $options);

        /* Set key prefix and separator. */
        $this->separator = empty($options[ProviderInterface::PROVIDER_OPT_SEPARATOR])
            ? ''
            : $options[ProviderInterface::PROVIDER_OPT_SEPARATOR];
        $this->prefix = empty($options[ProviderInterface::PROVIDER_OPT_PREFIX])
            ? ''
            : $options[ProviderInterface::PROVIDER_OPT_PREFIX] . $this->separator;
    }

    /**
     * Resolves and validates a set of options.
     *
     * @param OptionsResolver $optionsResolver
     * @param array           $options
     *
     * @returns array An array containing the resolved and validated options.
     *
     * @throws InvalidArgumentException
     */
    protected function resolveOptions(OptionsResolver $optionsResolver, array $options)
    {
        try {
            $options = $optionsResolver->resolve($options);
        } catch (\Exception $e) {
            throw new InvalidArgumentException(
                sprintf(
                    'Invalid configuration for cache provider "%s": %s',
                    $this->getProviderName(),
                    $e->getMessage()
                )
            );
        }

        return $options;
    }

    /**
     * Returns the name of the provider.
     *
     * @return string
     */
    protected abstract function getProviderName();

    /**
     * Generate a composite key based on the prefix and namespace (if one is defined).
     *
     * @param string $key     Base key.
     * @param array  $options Additional options.
     *
     * @return string The generated key.
     */
    protected function getKey($key, array $options)
    {
        /* Fetch additional options. */
        $namespace = isset($options[ProviderInterface::CMD_OPT_NAMESPACE])
            ? $options[ProviderInterface::CMD_OPT_NAMESPACE]
            : null;
        $namespaceExpiration = isset($options[ProviderInterface::CMD_OPT_NAMESPACE_EXPIRATION])
            ? $options[ProviderInterface::CMD_OPT_NAMESPACE_EXPIRATION]
            : null;

        /* No namespace used, simple key generation. */
        if (empty($namespace)) {

            return $this->prefix . $key;
        }

        /* Fetch the namespace version. */
        $namespaceKey = $this->prefix . $namespace;
        $namespaceVersion = $this->get($namespaceKey);

        /* If the namespace version is not set, generate a new one and set it. */
        if (! $namespaceVersion->getResult()) {
            $namespaceVersion = (string) $this->generateNamespaceVersion();
            $this->set($namespaceKey, $namespaceVersion, $namespaceExpiration);
        }

        /* Create and return the complete key. */
        return sprintf(
            '%s%s%s%s%s%s',
            $this->prefix,
            $namespace,
            $this->separator,
            $namespaceVersion->getResult(),
            $this->separator,
            $key
        );
    }

    /**
     * Generates the default value for the namespace (millis since epoch).
     *
     * @return integer
     */
    protected function generateNamespaceVersion()
    {
        return (int) round(microtime(true) * 1000);
    }
}
