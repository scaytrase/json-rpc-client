<?php
/**
 * User: scaytrase
 * Date: 2016-01-03
 * Time: 10:49
 */

namespace ScayTrase\Api\JsonRpc;

use ScayTrase\Api\Rpc\RpcErrorInterface;

interface JsonRpcErrorInterface extends RpcErrorInterface, \JsonSerializable
{
    const ERROR_CODE_FIELD = 'code';
    const ERROR_MESSAGE_FIELD = 'message';
    const ERROR_DATA_FIELD = 'data';

    CONST PARSE_ERROR = -32700;
    CONST INVALID_REQUEST = -32600;
    CONST METHOD_NOT_FOUND = -32601;
    CONST INVALID_PARAMS = -32602;
    CONST INTERNAL_ERROR = -32603;

    /**
     * Returns error code
     *
     * A Number that indicates the error type that occurred.
     * This MUST be an integer.
     *
     * @return int
     */
    public function getCode();

    /**
     * Return error message
     *
     * String providing a short description of the error.
     * The message SHOULD be limited to a concise single sentence.
     *
     * @return string
     */
    public function getMessage();

    /**
     * Returns amy additional error information specified by server
     *
     * A Primitive or Structured value that contains additional information about the error.
     * This may be omitted.
     * The value of this member is defined by the Server (e.g. detailed error information, nested errors etc.).
     *
     * @return \stdClass|mixed|null error data
     */
    public function getData();
}
