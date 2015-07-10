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
use Predis\ClientInterface;
use Predis\Response\Status;

class PredisProvider extends BaseProvider
{
    /**
     * @const string
     */
    const PROVIDER_NAME = 'Predis';

    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * Constructor.
     *
     * @param ClientInterface $client Predis client instance.
     */
    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    public function get($key, array $options = array())
    {
        $key = $this->getKey($key, $options);
        $data = $this->client->get($key);

        /* Null data represents that the key was not found, instruction error. */
        if (null === $data) {

            return new CacheResponse(false, false, true);
        }

        return new CacheResponse($this->unpackData($data), true, true);
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value, $expiration = null, array $options = array())
    {
        $key = $this->getKey($key, $options);
        $value = $this->packData($value);

        /* Use the correct form of the method. */
        $result = is_int($expiration) && 1 <= $expiration
            ? $this->client->set($key, $value, 'ex', $expiration)
            : $this->client->set($key, $value);

        return new CacheResponse($this->isStatusOk($result), $this->isStatusOk($result), true);
    }

    /**
     * {@inheritdoc}
     */
    public function increment($key, $increment = 1, $initialValue = 0, $expiration = null, array $options = array())
    {
        //@TODO
        $key = $this->getKey($key, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function lock($key, $owner = null, $expiration = null, array $options = array())
    {
        $key = $this->getKey($key, $options);
        $value = $this->packData($owner);

        /* Call set when dealing with expiration. */
        if (is_int($expiration) && 1 <= $expiration) {
            $result = $this->client->set($key, $value, 'ex', $expiration, 'nx');

            /* There's really no "failure" state here, errors are masked by the client. */
            return new CacheResponse($this->isStatusOk($result), true, true);
        }

        /* Call SETNX when no expiration is needed. */
        $result = $this->client->setnx($key, $value);

        /* Errors are masked, always return success. */
        return new CacheResponse($result, true, true);
    }

    /**
     * {@inheritdoc}
     */
    public function lockExists($key, array $options = array())
    {
        $key = $this->getKey($key, $options);
        $result = $this->client->exists($key);

        return new CacheResponse($result, true, true);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($key, array $options = array())
    {
        $key = $this->getKey($key, $options);
        $result = $this->client->del($key);

        /* "del" returns the number of of deleted keys, so 0 or a non-integer results are failures. */
        return is_int($result) && 1 <= $result
            ? new CacheResponse(true, true, true)
            : new CacheResponse(false, false, true);
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
    protected function getProviderName()
    {
        return self::PROVIDER_NAME;
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
     * Checks if a given response is a "status OK" one.
     *
     * @param mixed $response
     *
     * @return boolean TRUE when it is a "status OK" response, FALSE otherwise.
     */
    protected function isStatusOk($response)
    {
        return $response instanceof Status && 'OK' === $response->getPayload();
    }
}
