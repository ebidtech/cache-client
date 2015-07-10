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
     * Constructor.
     *
     * @param mixed   $result             Cache call result.
     * @param boolean $instructionSuccess Indicates if the instruction was successful.
     * @param boolean $connectionSuccess  Indicates if the connection was successful.
     */
    public function __construct($result, $instructionSuccess, $connectionSuccess)
    {
        $this->result = $result;
        $this->instructionSuccess = $instructionSuccess;
        $this->connectionSuccess = $connectionSuccess;
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
    public function isConnectionSuccess()
    {
        return $this->connectionSuccess;
    }

    /**
     * Checks if the instruction was successful.
     *
     * @return boolean
     */
    public function isInstructionSuccess()
    {
        return $this->instructionSuccess;
    }

    /**
     * Checks if the call was executed successfully (both the instruction and the connection worked).
     *
     * @return boolean
     */
    public function isSuccess()
    {
        return $this->instructionSuccess && $this->connectionSuccess;
    }
}
