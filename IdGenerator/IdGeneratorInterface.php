<?php

namespace ScayTrase\Api\IdGenerator;

use ScayTrase\Api\Rpc\RpcRequestInterface;

interface IdGeneratorInterface
{
    /**
     * @param RpcRequestInterface|null $request
     * @return string
     */
    public function getRequestIdentifier(RpcRequestInterface $request = null);
}
