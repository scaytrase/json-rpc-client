<?php
/**
 * User: scaytrase
 * Date: 2016-01-03
 * Time: 13:15
 */

namespace ScayTrase\Api\JsonRpc;

use ScayTrase\Api\Rpc\RpcResponseInterface;

interface JsonRpcResponseInterface extends RpcResponseInterface, \JsonSerializable
{
    const VERSION_FIELD = 'jsonrpc';
    const ID_FIELD = 'id';
    const ERROR_FIELD = 'error';
    const RESULT_FIELD = 'result';

    /**
     * Returns version of the JSON-RPC request
     *
     * A String specifying the version of the JSON-RPC protocol. MUST be exactly "2.0".
     *
     * @return string JSON-RPC version
     */
    public function getVersion();

    /**
     * Returns ID of response or NULL if request was notification
     *
     * This member is REQUIRED.
     * It MUST be the same as the value of the id member in the Request Object.
     *
     * @return string|null Response ID or null for notification pseudo-response
     */
    public function getId();

    /**
     * Returns JSON-RPC Error object or null if request was successful
     *
     * This member is REQUIRED on error.
     * This member MUST NOT exist if there was no error triggered during invocation.
     * The value for this member MUST be an Object as defined in section 5.1.
     *
     * @return JsonRpcErrorInterface|null
     */
    public function getError();

    /**
     * Returns result of JSON-RPC request
     *
     * This member is REQUIRED on success.
     * This member MUST NOT exist if there was an error invoking the method.
     * The value of this member is determined by the method invoked on the Server.
     *
     * @return \stdClass|array|mixed|null
     */
    public function getBody();
}
