<?php
/**
 * User: scaytrase
 * Date: 2016-01-02
 * Time: 21:38
 */

namespace ScayTrase\Api\Rpc;

interface RpcResponseInterface
{
    /** @return bool */
    public function isSuccessful();

    /** @return RpcErrorInterface|null */
    public function getError();

    /** @return \stdClass|array|mixed|null */
    public function getBody();
}
