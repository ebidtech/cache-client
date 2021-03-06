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
use EBT\CacheClient\Exception\InvalidArgumentException;
use EBT\CacheClient\Service\ProviderServiceInterface;
use EBT\Validator\Service\Validator\ValidatorService;
use EBT\Validator\Service\ValidatorServiceInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class BaseProviderService implements ProviderServiceInterface
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
        if (! $this->validator->isRequiredStringNotEmpty($key)) {

            return new CacheResponse(
                false,
                false,
                true,
                $this->validator->getLastError()
            );
        }

        return $this->doGet($key, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value, $expiration = null, array $options = array())
    {
        /* Validate parameters. */
        switch (false) {
            case $this->validator->isRequiredStringNotEmpty($key):
            case $this->validator->isRequiredScalar($value):
            case $this->validator->isOptionalPositiveInteger($expiration):
                return new CacheResponse(
                    false,
                    false,
                    true,
                    $this->validator->getLastError()
                );
        }

        return $this->doSet($key, $value, $expiration, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($key, array $options = array())
    {
        /* Validate the key. */
        if (! $this->validator->isRequiredStringNotEmpty($key)) {

            return new CacheResponse(
                false,
                false,
                true,
                $this->validator->getLastError()
            );
        }

        return $this->doDelete($key, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function increment($key, $increment = 1, $initialValue = 0, $expiration = null, array $options = array())
    {
        /* Validate parameters. */
        switch (false) {
            case $this->validator->isRequiredStringNotEmpty($key):
            case $this->validator->isRequiredPositiveInteger($increment):
            case $this->validator->isRequiredZeroPositiveInteger($initialValue):
            case $this->validator->isOptionalPositiveInteger($expiration):
                return new CacheResponse(
                    false,
                    false,
                    true,
                    $this->validator->getLastError()
                );
        }

        return $this->doIncrement($key, $increment, $initialValue, $expiration, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function flush($namespace)
    {
        /* Validate the key. */
        if (! $this->validator->isRequiredStringNotEmpty($namespace)) {

            return new CacheResponse(
                false,
                false,
                true,
                $this->validator->getLastError()
            );
        }

        return $this->doFlush($namespace);
    }

    /**
     * Configures the provider with a pre resolved array of values.
     *
     * @param array $options Options.
     *
     * @returns array An array containing the resolved and validated options.
     *
     * @throws InvalidArgumentException
     */
    protected function setOptions(array $options)
    {
        /* Configure the options resolver. */
        $optionsResolver = new OptionsResolver();
        $this->configureProviderOptions($optionsResolver);

        /* Resolve the options. */
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

        /* Set key prefix and separator. */
        $this->separator = $options[ProviderServiceInterface::PROVIDER_OPT_SEPARATOR];
        $this->prefix    = $options[ProviderServiceInterface::PROVIDER_OPT_PREFIX];

        /* Add separator to the end of the default prefix if applicable. */
        if (! empty($this->prefix)) {
            $this->prefix .= $this->separator;
        }

        return $options;
    }

    /**
     * Configures provider specific options.
     *
     * @param OptionsResolver $optionsResolver Options resolver instance.
     *
     * @return OptionsResolver
     */
    protected function configureProviderOptions(OptionsResolver $optionsResolver)
    {
        /* Set default values. */
        $optionsResolver->setDefault(ProviderServiceInterface::PROVIDER_OPT_PREFIX, '');
        $optionsResolver->setDefault(ProviderServiceInterface::PROVIDER_OPT_SEPARATOR, '');

        /* Set allowed types. */
        $optionsResolver->setAllowedTypes(ProviderServiceInterface::PROVIDER_OPT_PREFIX, 'string');
        $optionsResolver->setAllowedTypes(ProviderServiceInterface::PROVIDER_OPT_SEPARATOR, 'string');
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
        $namespace           = $this->getArrayValueOrDefault($options[ProviderServiceInterface::CMD_OPT_NAMESPACE]);
        $namespaceExpiration = $this->getArrayValueOrDefault(
            $options[ProviderServiceInterface::CMD_OPT_NAMESPACE_EXPIRATION]
        );

        /* No namespace used, simple key generation. */
        if (empty($namespace)) {

            return $this->prefix . $key;
        }

        /* Fetch the namespace version. */
        $namespaceVersion = $this->get($namespace)
            ->getResult();

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
        return (int) round(microtime(true) * 1000);
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
     * @see ProviderServiceInterface::get
     *
     * @param string $key     The key to fetch.
     * @param array  $options Additional options.
     *
     * @return CacheResponse
     */
    abstract protected function doGet($key, array $options = array());

    /**
     * Sets a new value in the cache.
     *
     * @see ProviderServiceInterface::set
     *
     * @param string       $key        Key to set.
     * @param mixed        $value      Value to set (any scalar value).
     * @param integer|null $expiration Key TTL.
     * @param array        $options    Additional options.
     *
     * @return CacheResponse
     */
    abstract protected function doSet($key, $value, $expiration, array $options = array());

    /**
     * Deletes a single key.
     *
     * @see ProviderServiceInterface::delete
     *
     * @param string $key     Key to delete.
     * @param array  $options Additional options.
     *
     * @return CacheResponse
     */
    abstract protected function doDelete($key, array $options = array());

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
    abstract protected function doIncrement(
        $key,
        $increment = 1,
        $initialValue = 0,
        $expiration = null,
        array $options = array()
    );

    /**
     * Deletes all cached keys in a namespace. This operation is not guaranteed to delete the affected
     * keys, it only ensures a "logical delete" (those keys are no longer accessible within the namespace).
     *
     * @see ProviderServiceInterface::flush
     *
     * @param string $namespace
     *
     * @return CacheResponse
     */
    abstract protected function doFlush($namespace);

    /**
     * Retrieves the provider's name.
     *
     * @return string
     */
    abstract protected function getProviderName();
}
