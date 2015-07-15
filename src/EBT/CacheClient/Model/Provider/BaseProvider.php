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

use EBT\CacheClient\Entity\CacheResponse;
use EBT\CacheClient\Exception\InvalidArgumentException;
use EBT\CacheClient\Model\ProviderInterface;
use EBT\Validator\Service\Validator\ValidatorService;
use EBT\Validator\Service\ValidatorServiceInterface;
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
     * @var ValidatorServiceInterface
     */
    protected $validator;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->validator = new ValidatorService();
    }

    /**
     * {@inheritdoc}
     */
    public function get($key, array $options = array())
    {
        /* Validate the key. */
        if (! $this->getValidator()->requiredStringNotEmpty($key, __METHOD__, 'key')) {
            return new CacheResponse(false, false, true, $this->getValidator()->getLastError());
        }

        return $this->doGet($key, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value, $expiration = null, array $options = array())
    {
        /* Validate parameters. */
        /*switch (false) {
            case $this->getValidator()->requiredStringNotEmpty($key, __METHOD__, 'key'):
            case $this->getValidator()->requiredString($value, __METHOD__, 'value'):
            case $this->getValidator()->optionalPositiveInteger($expiration, __METHOD__, 'expiration'):

                return new CacheResponse(false, false, true, $this->getValidator()->getLastError());
        }*/

        return $this->doSet($key, $value, $expiration, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($key, array $options = array())
    {
        /* Validate the key. */
        if (! $this->getValidator()->requiredStringNotEmpty($key, __METHOD__, 'key')) {
            return new CacheResponse(false, false, true, $this->getValidator()->getLastError());
        }

        return $this->doDelete($key, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function flush($namespace)
    {        /* Validate the key. */
        if (! $this->getValidator()->requiredStringNotEmpty($namespace, __METHOD__, 'namespace')) {
            return new CacheResponse(false, false, true, $this->getValidator()->getLastError());
        }

        return $this->doFlush($namespace);
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
        $options = $this->resolveOptions($optionsResolver, $options);

        /* Set key prefix and separator. */
        $this->separator = $options[ProviderInterface::PROVIDER_OPT_SEPARATOR];
        $this->prefix    = $options[ProviderInterface::PROVIDER_OPT_PREFIX];

        /* Add separator to the end of the default prefix if applicable. */
        if (! empty($this->prefix)) {
            $this->prefix .= $this->separator;
        }
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
        $optionsResolver->setDefault(ProviderInterface::PROVIDER_OPT_PREFIX, '');
        $optionsResolver->setDefault(ProviderInterface::PROVIDER_OPT_SEPARATOR, '');

        /* Set allowed types. */
        $optionsResolver->setAllowedTypes(ProviderInterface::PROVIDER_OPT_PREFIX, 'string');
        $optionsResolver->setAllowedTypes(ProviderInterface::PROVIDER_OPT_SEPARATOR, 'string');
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
        $namespace = $this->getArrayValueOrDefault($options[ProviderInterface::CMD_OPT_NAMESPACE]);
        $namespaceExpiration = $this->getArrayValueOrDefault(
            $options[ProviderInterface::CMD_OPT_NAMESPACE_EXPIRATION]
        );

        /* No namespace used, simple key generation. */
        if (empty($namespace)) {

            return $this->prefix . $key;
        }

        /* Fetch the namespace version. */
        $namespaceVersion = $this->get($namespace)->getResult();

        /* If the namespace version is not set, generate a new one and set it. */
        if (! $namespaceVersion) {
            $namespaceVersion = (string) $this->generateNamespaceVersion();
            $this->set($namespace, $namespaceVersion, $namespaceExpiration);
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
        return (int)round(microtime(true) * 1000);
    }

    /**
     * Helper method to encapsulate array key checks and default value setting.
     *
     * @param mixed $value
     * @param mixed $default
     *
     * @return mixed
     */
    protected function getArrayValueOrDefault(&$value, $default = null)
    {
        return isset($value) ? $value : $default;
    }

    /**
     * Retrieves the validator service instance.
     *
     * @return ValidatorServiceInterface
     */
    protected function getValidator()
    {
        return $this->validator;
    }

    /**
     * Fetches the value stored under a given key.
     *
     * @see ProviderInterface::get
     *
     * @param string $key     The key to fetch.
     * @param array  $options Additional options.
     *
     * @return CacheResponse
     */
    protected abstract function doGet($key, array $options = array());

    /**
     * Sets a new value in the cache.
     *
     * @see ProviderInterface::set
     *
     * @param string       $key        Key to set.
     * @param string       $value      Value to set.
     * @param integer|null $expiration Key TTL.
     * @param array        $options    Additional options.
     *
     * @return CacheResponse
     */
    protected abstract function doSet($key, $value, $expiration, array $options = array());

    /**
     * Deletes a single key.
     *
     * @see ProviderInterface::delete
     *
     * @param string $key     Key to delete.
     * @param array  $options Additional options.
     *
     * @return CacheResponse
     */
    protected abstract function doDelete($key, array $options = array());

    /**
     * Deletes all cached keys in a namespace. This operation is not guaranteed to delete the affected
     * keys, it only ensures a "logical delete" (those keys are no longer accessible within the namespace).
     *
     * @see ProviderInterface::flush
     *
     * @param string $namespace
     *
     * @return CacheResponse
     */
    protected abstract function doFlush($namespace);

    /**
     * Retrieves the provider's name.
     *
     * @return string
     */
    protected abstract function getProviderName();
}
