<?php
/**
 * User: scaytrase
 * Date: 2016-01-03
 * Time: 13:15
 */

namespace ScayTrase\Api\JsonRpc;

use ScayTrase\Api\Rpc\RpcResponseInterface;

interface JsonRpcResponseInterface extends RpcResponseInterface
{
    const VERSION_FIELD = 'jsonrpc';
    const ID_FIELD = 'id';
    const ERROR_FIELD = 'error';
    const ERROR_CODE_FIELD = 'code';
    const ERROR_MESSAGE_FIELD = 'message';
    const ERROR_DATA_FIELD = 'data';
    const RESULT_FIELD = 'result';

    /**
     * @return string JSON-RPC version
     */
    public function getVersion();

    /** @return string|null Response ID or null for notification pseudo-response */
    public function getId();
}
