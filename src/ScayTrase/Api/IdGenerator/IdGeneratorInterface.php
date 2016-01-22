<?php
/**
 * User: scaytrase
 * Date: 2016-01-02
 * Time: 21:57
 */

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
