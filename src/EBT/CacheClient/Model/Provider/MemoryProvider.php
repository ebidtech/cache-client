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
use EBT\CacheClient\Model\ProviderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MemoryProvider extends BaseProvider
{
    const KEY_VALUE = 'value';
    const KEY_TTL   = 'ttl';

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
     * {@inheritdoc}
     */
    public function get($key, array $options = array())
    {
        $this->collectGarbage();
        $key = $this->getKey($key, $options);
        $info = $this->getKeyInfo($key);

        /* Key does not exist. */
        if (empty($info)) {

            return new CacheResponse(false, false);
        }

        return new CacheResponse(
            $this->unpackData($info[self::KEY_VALUE]),
            true
        );
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value, $expiration = null, array $options = array())
    {
        $this->collectGarbage();

        /* Serializing data to make this as close as possible to the other storage providers. */
        $this->memory[$this->getKey($key, $options)] = array(
            self::KEY_VALUE => $this->packData($value),
            self::KEY_TTL => $this->getExpirationTimestamp($expiration)
        );

        /* Currently we have no reason for this to fail. */
        return new CacheResponse(true, true);
    }

    /**
     * {@inheritdoc}
     */
    public function increment($key, $increment = 1, $initialValue = 0, $expiration = null, array $options = array())
    {
        /* Retrieve the value. */
        $newKey = $this->getKey($key, $options);
        $info = $this->getKeyInfo($newKey);

        /* Key does not exist, create is as usual. */
        if (empty($info)) {

            return $this->set($key, $increment + $initialValue, $expiration, $options);
        }

        /* The key exists, so let's try to increment (only for integer values). */
        $value = $this->unpackData($info[self::KEY_VALUE]);

        if (! is_int($value)) {

            /* This one also represents an operation failure. */
            return new CacheResponse(false, false);
        }

        /* Everything looks good, increment the value, store it and return the new value. */
        $value += $increment;
        $this->memory[$newKey][self::KEY_VALUE] = $this->packData($value);

        return new CacheResponse($value, true);
    }

    /**
     * {@inheritdoc}
     */
    public function lock($key, $owner = null, $expiration = null, array $options = array())
    {
        $info = $this->getKeyInfo($this->getKey($key, $options));

        /* We can set the lock if it doesn't exist. */
        return empty($info)
            ? $this->set($key, $owner, $expiration, $options)
            : new CacheResponse(false, true);
    }

    /**
     * {@inheritdoc}
     */
    public function lockExists($key, array $options = array())
    {
        $info = $this->getKeyInfo($this->getKey($key, $options));

        /* No motives for this to fail. */
        return empty($info)
            ? new CacheResponse(false, true)
            : new CacheResponse(true, true);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($key, array $options = array())
    {
        $key = $this->getKey($key, $options);
        $info = $this->getKeyInfo($key);

        /* The given key did not exist, this is a failure. */
        if (empty($info)) {

            return new CacheResponse(false, false);
        }

        /* Everything looks good, delete the key. */
        unset($this->memory[$key]);

        return new CacheResponse(true, true);
    }

    /**
     * {@inheritdoc}
     */
    public function flush($namespace)
    {
        return $this->delete($namespace);
    }

    /**
     * {@inheritdoc}
     */
    public function setProviderOptions(array $options)
    {
        parent::setProviderOptions($options);

        /* Set provider specific options. */
        $this->gcProbability =
            (float) $options[ProviderInterface::PROVIDER_OPT_GC_PROBABILITY] /
            (float) $options[ProviderInterface::PROVIDER_OPT_GC_DIVISOR];
    }

    /**
     * {@inheritdoc}
     */
    public function configureProviderOptions(OptionsResolver $optionsResolver)
    {
        /* Parent options still apply. */
        parent::configureProviderOptions($optionsResolver);

        /* Add allowed types. */
        $optionsResolver->addAllowedTypes(ProviderInterface::PROVIDER_OPT_GC_PROBABILITY, array('int'));
        $optionsResolver->addAllowedTypes(ProviderInterface::PROVIDER_OPT_GC_DIVISOR, array('int'));

        /* Define default values. */
        $optionsResolver->setDefault(ProviderInterface::PROVIDER_OPT_GC_PROBABILITY, 1);
        $optionsResolver->setDefault(ProviderInterface::PROVIDER_OPT_GC_DIVISOR, 100);

        /* Set allowed values. */
        $optionsResolver->setAllowedValues(
            ProviderInterface::PROVIDER_OPT_GC_PROBABILITY,
            function ($value) {

                return 0 <= $value;
            }
        );
        $optionsResolver->setAllowedValues(
            ProviderInterface::PROVIDER_OPT_GC_DIVISOR,
            function ($value) {

                return 1 <= $value;
            }
        );
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

    /**
     * Checks if a certain time to live has already expired.
     *
     * @param integer $timestamp
     *
     * @return boolean TRUE if it is expired, FALSE otherwise.
     */
    protected function isExpired($timestamp)
    {
        return is_int($timestamp) || time() > $timestamp;
    }

    /**
     * Packs data for storage.
     *
     * @param mixed $data The data to be packed.
     *
     * @return string The packed data.
     */
    protected function packData($data)
    {
        return serialize($data);
    }

    /**
     * Unpacks data for usage.
     *
     * @param mixed $data The data to be unpacked.
     *
     * @return string The unpacked data.
     */
    protected function unpackData($data)
    {
        return unserialize($data);
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
}
