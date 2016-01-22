<?php
/**
 * User: scaytrase
 * Date: 2016-01-03
 * Time: 10:19
 */

namespace ScayTrase\Api\Rpc;

interface RpcErrorInterface
{
    /** @return int */
    public function getCode();

    /** @return string */
    public function getMessage();
}
