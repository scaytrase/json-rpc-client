<?php
/**
 * User: scaytrase
 * Date: 2016-01-03
 * Time: 11:02
 */

namespace ScayTrase\Api\JsonRpc;

use ScayTrase\Api\Rpc\RpcRequestInterface;

final class JsonRpcRequest implements JsonRpcRequestInterface
{
    /** @var  string */
    private $id;
    /** @var  string */
    private $method;
    /** @var  \StdClass|\StdClass[]|null */
    private $parameters;

    /**
     * JsonRpcRequest constructor.
     * @param string $method
     * @param \StdClass|\StdClass[]|null $parameters
     * @param string $id
     */
    public function __construct($method, $parameters = null, $id = null)
    {
        $this->method = $method;
        $this->parameters = $parameters;
        $this->id = $id;
    }

    /**
     * @param RpcRequestInterface $request
     * @param string $id
     * @return static
     */
    public static function fromRpcRequest(RpcRequestInterface $request, $id)
    {
        return new static($request->getMethod(), $request->getParameters(), $id);
    }

    /** {@inheritdoc} */
    public function isNotification()
    {
        return false;
    }

    /** {@inheritdoc} */
    public function getId()
    {
        return $this->id;
    }

    /** {@inheritdoc} */
    public function getMethod()
    {
        return $this->method;
    }

    /** {@inheritdoc} */
    public function getParameters()
    {
        return $this->parameters;
    }
}
