<?php

namespace ScayTrase\Api\JsonRpc;

use ScayTrase\Api\Rpc\RpcRequestInterface;

final class JsonRpcRequest implements JsonRpcRequestInterface
{
    /** @var  string */
    private $id;
    /** @var  string */
    private $method;
    /** @var  \stdClass|array|null */
    private $parameters;

    /**
     * JsonRpcRequest constructor.
     * @param string $method
     * @param \stdClass|array|null $parameters
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

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        $result = [
            self::VERSION_FIELD => JsonRpcClient::VERSION,
            self::METHOD_FIELD => $this->getMethod(),
            self::PARAMETERS_FIELD => $this->getParameters(),
        ];

        if (!$this->isNotification()) {
            $result[self::ID_FIELD] = $this->getId();
        }

        return $result;
    }

    /**
     * Returns version of the JSON-RPC request
     *
     * A String specifying the version of the JSON-RPC protocol. MUST be exactly "2.0".
     *
     * @return string JSON-RPC version
     */
    public function getVersion()
    {
        return JsonRpcClient::VERSION;
    }
}
