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
     *
     * @param string               $method
     * @param \stdClass|array|null $parameters
     * @param string               $id
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($method, $parameters = null, $id)
    {
        $this->method     = (string)$method;
        $this->parameters = $parameters;
        $this->id         = (string)$id;

        if (empty($this->id)) {
            throw new \InvalidArgumentException(
                'ID should not be empty for JSON-RPC request, use notification instead'
            );
        }
    }

    /**
     * @param RpcRequestInterface $request
     * @param string              $id
     *
     * @return static
     *
     * @throws \InvalidArgumentException
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
        return [
            self::VERSION_FIELD    => $this->getVersion(),
            self::ID_FIELD         => $this->getId(),
            self::METHOD_FIELD     => $this->getMethod(),
            self::PARAMETERS_FIELD => $this->getParameters(),
        ];
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
