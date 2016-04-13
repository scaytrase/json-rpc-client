<?php
/**
 * User: scaytrase
 * Date: 2016-01-03
 * Time: 10:55
 */

namespace ScayTrase\Api\JsonRpc;

use ScayTrase\Api\Rpc\RpcRequestInterface;

/**
 * Interface JsonRpcRequestInterface
 * @link http://www.jsonrpc.org/specification
 */
interface JsonRpcRequestInterface extends RpcRequestInterface, \JsonSerializable
{
    const VERSION_FIELD = 'jsonrpc';
    const ID_FIELD = 'id';
    const PARAMETERS_FIELD = 'params';
    const METHOD_FIELD = 'method';

    /**
     * Returns version of the JSON-RPC request
     *
     * A String specifying the version of the JSON-RPC protocol. MUST be exactly "2.0".
     *
     * @return string JSON-RPC version
     */
    public function getVersion();

    /**
     * Return JSON-RPC request identifier or NULL for notification request
     *
     * An identifier established by the Client that MUST contain a String, Number, or NULL value if included.
     * If it is not included it is assumed to be a notification.
     *
     * @return int|null Id. if not a notification and id is not set - id should be automatically generated
     */
    public function getId();

    /**
     * Indicates if request is notification
     *
     * @return bool True if request should not receive response from the server
     */
    public function isNotification();

    /**
     * Returns JSON-RPC request method
     *
     * A String containing the name of the method to be invoked. Method names that begin with the word rpc
     * followed by a period character (U+002E or ASCII 46) are reserved for rpc-internal methods and extensions
     * and MUST NOT be used for anything else.
     *
     * @return string
     */
    public function getMethod();

    /**
     * Returns JSON-RPC request parameters or null if omitted
     *
     * A Structured value that holds the parameter values to be used during the invocation of the method. This member MAY be omitted.
     *
     * If present, parameters for the rpc call MUST be provided as a Structured value. Either by-position through an Array or by-name through an Object.
     *
     * * by-position:   params MUST be an Array, containing the values in the Server expected order.
     * * by-name:       params MUST be an Object, with member names that match the Server expected parameter names.
     *                  The absence of expected names MAY result in an error being generated. The names MUST match
     *                  exactly, including case, to the method's expected parameters.
     *
     * @return \stdClass|array|null
     */
    public function getParameters();
}
