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
use EBT\CacheClient\Service\ProviderServiceInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MemoryProviderService extends BaseProviderService
{
    /**
     * @const string
     */
    const PROVIDER_NAME = 'Memory';

    /**
     * @const string
     */
    const KEY_VALUE = 'value';

    /**
     * @const string
     */
    const KEY_TTL = 'ttl';

    /**
     * @var integer
     */
    protected $maxKeys;

    /**
     * @var float
     */
    protected $gcProbability;

    /**
     * @var array
     */
    protected $memory = array();

    /**
     * Constructor.
     *
     * @param array $options Provider options.
     */
    public function __construct(array $options = array())
    {
        parent::__construct();

        /* Set the provider options. */
        $this->setOptions($options);
    }

    /**
     * {@inheritdoc}
     */
    protected function doGet($key, array $options = array())
    {
        $this->collectGarbage();
        $info = $this->getKeyInfo($this->getKey($key, $options));

        /* Key does not exist. */
        if (empty($info)) {

            return new CacheResponse(false, false, true, CacheResponse::RESOURCE_NOT_FOUND);
        }

        return new CacheResponse($info[self::KEY_VALUE], true, true);
    }

    /**
     * {@inheritdoc}
     */
    protected function doSet($key, $value, $expiration = null, array $options = array())
    {
        $this->collectGarbage();

        /* Serializing data to make this as close as possible to the other storage providers. */
        $this->memory[$this->getKey($key, $options)] = array(
            self::KEY_VALUE => $value,
            self::KEY_TTL   => $this->getExpirationTimestamp($expiration),
        );

        /* Currently we have no reason for this to fail. */
        return new CacheResponse(true, true, true);
    }

    /**
     * {@inheritdoc}
     */
    protected function doDelete($key, array $options = array())
    {
        $key  = $this->getKey($key, $options);
        $info = $this->getKeyInfo($key);

        /* The given key did not exist, this is a failure. */
        if (empty($info)) {

            return new CacheResponse(false, false, true, CacheResponse::RESOURCE_NOT_FOUND);
        }

        /* Everything looks good, delete the key. */
        unset($this->memory[$key]);

        return new CacheResponse(true, true, true);
    }

    /**
     * {@inheritdoc}
     */
    protected function doFlush($namespace)
    {
        return $this->delete($namespace);
    }

    /**
     * {@inheritdoc}
     */
    protected function getProviderName()
    {
        return self::PROVIDER_NAME;
    }

    /**
     * {@inheritdoc}
     */
    protected function setOptions(array $options)
    {
        $options = parent::setOptions($options);

        /* Set provider specific options. */
        $this->gcProbability =
            (float) $options[ProviderServiceInterface::PROVIDER_OPT_GC_PROBABILITY] /
            (float) $options[ProviderServiceInterface::PROVIDER_OPT_GC_DIVISOR];

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureProviderOptions(OptionsResolver $optionsResolver)
    {
        /* Parent options still apply. */
        parent::configureProviderOptions($optionsResolver);

        /* Define default values. */
        $optionsResolver->setDefault(ProviderServiceInterface::PROVIDER_OPT_GC_PROBABILITY, 1);
        $optionsResolver->setDefault(ProviderServiceInterface::PROVIDER_OPT_GC_DIVISOR, 100);

        /* Add allowed types. */
        $optionsResolver->setAllowedTypes(ProviderServiceInterface::PROVIDER_OPT_GC_PROBABILITY, 'int');
        $optionsResolver->setAllowedTypes(ProviderServiceInterface::PROVIDER_OPT_GC_DIVISOR, 'int');

        /* Set allowed values. */
        $optionsResolver->setAllowedValues(
            ProviderServiceInterface::PROVIDER_OPT_GC_PROBABILITY,
            function ($value) {

                return 0 <= $value;
            }
        );
        $optionsResolver->setAllowedValues(
            ProviderServiceInterface::PROVIDER_OPT_GC_DIVISOR,
            function ($value) {

                return 1 <= $value;
            }
        );
    }

    /**
     * Removes any expired keys (effective removal is based on a random percentage).
     */
    protected function collectGarbage()
    {
        /* Don't apply the garbage collector. */
        if (((float) mt_rand() / (float) mt_getrandmax()) >= $this->gcProbability) {

            return;
        }

        /* Remove all expired keys. */
        foreach ($this->memory as $key => &$info) {
            if ($this->isExpired($info[self::KEY_TTL])) {
                unset($this->memory[$key]);
            }
        }
    }

    /**
     * Checks if a certain time to live has already expired.
     *
     * @param integer $timestamp
     *
     * @return boolean TRUE if it is expired, FALSE otherwise.
     */
    protected function isExpired($timestamp)
    {
        return is_int($timestamp) && time() >= $timestamp;
    }

    /**
     * Retrieves the key's info if it exists.
     *
     * @param string $key Key to lookup.
     *
     * @return array An array with the key's info, or an empty array when it does not exist.
     */
    protected function getKeyInfo($key)
    {
        /* They key does not exist, just return an empty array. */
        if (! isset($this->memory[$key])) {

            return array();
        }
        $info = $this->memory[$key];

        /* Check if the key is expired. */
        if ($this->isExpired($info[self::KEY_TTL])) {
            unset($this->memory[$key]);
            $info = array();
        }

        return $info;
    }

    /**
     * Retrieves the timestamp for the given expiration time.
     *
     * @param integer $expiration
     *
     * @return integer|null Integer for applicable expiration, null for unlimited expiration.
     */
    protected function getExpirationTimestamp($expiration)
    {
        return is_int($expiration)
            ? time() + $expiration
            : null;
    }
}
