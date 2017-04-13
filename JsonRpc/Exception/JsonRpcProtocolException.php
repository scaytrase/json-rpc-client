<?php

namespace ScayTrase\Api\JsonRpc\Exception;

use ScayTrase\Api\JsonRpc\JsonRpcRequestInterface;

class JsonRpcProtocolException extends \LogicException implements JsonRpcExceptionInterface
{
    /**
     * @param JsonRpcRequestInterface $request
     * @return static
     */
    public static function requestSendButNotResponded(JsonRpcRequestInterface $request)
    {
        return new static(sprintf(
            'Request with id "%s" was sent to endpoint, but output does not contain response for this request',
            $request->getId()
        ));
    }
}
