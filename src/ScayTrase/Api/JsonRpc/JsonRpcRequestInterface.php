<?php
/**
 * User: scaytrase
 * Date: 2016-01-03
 * Time: 10:55
 */

namespace ScayTrase\Api\JsonRpc;

use ScayTrase\Api\Rpc\RpcRequestInterface;

interface JsonRpcRequestInterface extends RpcRequestInterface
{
    /** @return int|null Id. if not a notification and id is not set - id should be automatically generated */
    public function getId();

    /** @return bool True if request should receive response from the server */
    public function isNotification();
}
