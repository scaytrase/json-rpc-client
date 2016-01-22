<?php
/**
 * User: scaytrase
 * Date: 2016-01-03
 * Time: 23:22
 */

namespace ScayTrase\Api\JsonRpc;

use ScayTrase\Api\Rpc\RpcErrorInterface;

final class JsonRpcNotificationResponse implements JsonRpcResponseInterface
{
    /**
     * @return \StdClass|null
     */
    public function getResult()
    {
        return null;
    }

    /**
     * @return string JSON-RPC version
     */
    public function getVersion()
    {
        return JsonRpcClient::VERSION;
    }

    /** @return bool */
    public function isSuccessful()
    {
        return true;
    }

    /** @return RpcErrorInterface|null */
    public function getError()
    {
        return null;
    }

    /** @return \StdClass[]|null */
    public function getBody()
    {
        return null;
    }

    /** @return array */
    public function getHeaders()
    {
        return [];
    }

    /** @return string|null Response ID or null for notification pseudo-response */
    public function getId()
    {
        return null;
    }
}
