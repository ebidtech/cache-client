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
     * Sets the default prefix and separator for the provider.
     *
     * @param array $options Provider configuration options.
     */
    public function setProviderOptions(array $options)
    {
        /* Set key prefix and separator. */
        $this->separator = empty($options[ProviderInterface::PROVIDER_OPT_SEPARATOR])
            ? ''
            : $options[ProviderInterface::PROVIDER_OPT_SEPARATOR];
        $this->prefix = empty($options[ProviderInterface::PROVIDER_OPT_PREFIX])
            ? ''
            : $options[ProviderInterface::PROVIDER_OPT_PREFIX] . $this->separator;
    }

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
        $optionsResolver->setDefaults(
            array(
                ProviderInterface::PROVIDER_OPT_PREFIX    => '',
                ProviderInterface::PROVIDER_OPT_SEPARATOR => ''
            )
        );

        /* Set allowed types. */
        $optionsResolver->setAllowedValues(
            array(
                ProviderInterface::PROVIDER_OPT_PREFIX    => 'string',
                ProviderInterface::PROVIDER_OPT_SEPARATOR => 'string'
            )
        );
    }

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
        if (null === $namespaceVersion) {
            $namespaceVersion = (string) $this->generateNamespaceVersion();
            $this->set($namespaceKey, $namespaceVersion, $namespaceExpiration);
        }

        /* Create and return the complete key. */
        return sprintf(
            '%s%s%s%s%s%s',
            $this->prefix,
            $namespace,
            $this->separator,
            $namespaceVersion,
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
