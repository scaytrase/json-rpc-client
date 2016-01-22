<?php
/**
 * User: scaytrase
 * Date: 2016-01-03
 * Time: 10:57
 */

namespace ScayTrase\Api\JsonRpc;

use ScayTrase\Api\IdGenerator\IdGeneratorInterface;

class JsonRpcRequestFactory
{
    /** @var  IdGeneratorInterface */
    private $idGenerator;

    /**
     * JsonRpcRequestFactory constructor.
     * @param IdGeneratorInterface $idGenerator
     */
    public function __construct(IdGeneratorInterface $idGenerator)
    {
        $this->idGenerator = $idGenerator;
    }

    /**
     * @param $method
     * @param array $parameters
     * @param string|null $id
     * @return JsonRpcRequestInterface
     */
    public function createRequest($method, array $parameters, $id = null)
    {
        return new JsonRpcRequest($method, $parameters, $id ?: $this->idGenerator->getRequestIdentifier());
    }

    /**
     * @param $method
     * @param array $parameters
     * @return JsonRpcRequestInterface
     */
    public function createNotification($method, array  $parameters)
    {
        return new JsonRpcNotification($method, $parameters);
    }
}
