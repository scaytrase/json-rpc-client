<?php
/**
 * User: scaytrase
 * Date: 2016-01-03
 * Time: 10:49
 */

namespace ScayTrase\Api\JsonRpc;

use ScayTrase\Api\Rpc\RpcErrorInterface;

interface JsonRpcErrorInterface extends RpcErrorInterface
{
    CONST PARSE_ERROR = -32700;
    CONST INVALID_REQUEST = -32600;
    CONST METHOD_NOT_FOUND = -32601;
    CONST INVALID_PARAMS = -32602;
    CONST INTERNAL_ERROR = -32603;

    /** @return \StdClass|null error data  */
    public function getData();
}
