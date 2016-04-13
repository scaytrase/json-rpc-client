<?php
/**
 * User: scaytrase
 * Date: 2016-01-03
 * Time: 13:00
 */

namespace ScayTrase\Api\JsonRpc;

use ScayTrase\Api\Rpc\RpcRequestInterface;

/** @internal */
final class RequestTransformation
{
    /** @var  RpcRequestInterface */
    private $originalCall;

    /** @var  JsonRpcRequestInterface */
    private $transformedCall;

    /**
     * RequestTransformation constructor.
     * @param RpcRequestInterface $originalCall
     * @param JsonRpcRequestInterface $transformedCall
     */
    public function __construct(RpcRequestInterface $originalCall, JsonRpcRequestInterface $transformedCall)
    {
        $this->originalCall = $originalCall;
        $this->transformedCall = $transformedCall;
    }

    /**
     * @return RpcRequestInterface
     */
    public function getOriginalCall()
    {
        return $this->originalCall;
    }

    /**
     * @return JsonRpcRequestInterface
     */
    public function getTransformedCall()
    {
        return $this->transformedCall;
    }

    /**
     * @return bool
     */
    public function isTransformed()
    {
        return $this->originalCall !== $this->transformedCall;
    }
}
