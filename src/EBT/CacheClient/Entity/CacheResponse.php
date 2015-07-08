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
    protected $status;

    /**
     * Constructor.
     *
     * @param mixed   $result  Cache call result.
     * @param boolean $status Indicates the status of the call.
     */
    public function __construct($result, $status)
    {
        $this->result = $result;
        $this->status = $status;
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
     * Checks if the call was executed successfully.
     *
     * @return boolean
     */
    public function isStatusOk()
    {
        return $this->status;
    }

    /**
     * Checks if the call failed.
     *
     * @return boolean
     */
    public function isStatusFailure()
    {
        return ! $this->status;
    }
}
