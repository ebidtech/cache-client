<?php

/**
 * This file is a part of the Cache Client library.
 *
 * (c) 2015 Ebidtech
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EBT\CacheClient\Entity;

class CacheResponse
{
    const RESOURCE_NOT_FOUND  = 'Resource not found.';
    const RESOURCE_NOT_STORED = 'Resource not stored.';
    const CONNECTION_ERROR    = 'Connection error.';

    /**
     * @var mixed
     */
    protected $result;

    /**
     * @var boolean
     */
    protected $connectionSuccess;

    /**
     * @var boolean
     */
    protected $instructionSuccess;

    /**
     * @var string
     */
    protected $errorMessage;

    /**
     * Constructor.
     *
     * @param mixed   $result             Cache call result.
     * @param boolean $instructionSuccess Indicates if the instruction was successful.
     * @param boolean $connectionSuccess  Indicates if the connection was successful.
     * @param string  $errorMessage       Error message to be set.
     */
    public function __construct($result, $instructionSuccess, $connectionSuccess, $errorMessage = null)
    {
        $this->result             = $result;
        $this->instructionSuccess = $instructionSuccess;
        $this->connectionSuccess  = $connectionSuccess;
        $this->errorMessage       = $errorMessage;
    }

    /**
     * Retrieves the call's result.
     *
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Checks if the connections was successful.
     *
     * @return boolean
     */
    public function isConnectionSuccessful()
    {
        return $this->connectionSuccess;
    }

    /**
     * Retrieves the error message if one exists.
     *
     * @return null|string
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * Checks if the instruction was successful.
     *
     * @return boolean
     */
    public function isInstructionSuccessful()
    {
        return $this->instructionSuccess;
    }

    /**
     * Checks if the call failed to execute (either by instruction or connection failure).
     *
     * @return boolean
     */
    public function isFailure()
    {
        return !$this->isSuccessful();
    }

    /**
     * Checks if the call was executed successfully (both the instruction and the connection worked).
     *
     * @return boolean
     */
    public function isSuccessful()
    {
        return $this->instructionSuccess && $this->connectionSuccess;
    }
}
