<?php
/**
 * User: scaytrase
 * Date: 2016-01-02
 * Time: 21:36
 */

namespace ScayTrase\Api\Rpc;

interface RpcRequestInterface
{
    /** @return string */
    public function getMethod();

    /** @return \stdClass|array|null */
    public function getParameters();
}
