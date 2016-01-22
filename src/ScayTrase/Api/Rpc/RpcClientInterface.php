<?php
/**
 * User: scaytrase
 * Date: 2016-01-02
 * Time: 21:35
 */

namespace ScayTrase\Api\Rpc;

use ScayTrase\Api\Rpc\Exception\RpcExceptionInterface;

interface RpcClientInterface
{
    /**
     * @param RpcRequestInterface|RpcRequestInterface[] $calls
     * @return ResponseCollectionInterface
     *
     * @throws RpcExceptionInterface
     */
    public function invoke($calls);
}
