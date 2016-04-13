<?php
/**
 * User: scaytrase
 * Date: 2016-01-03
 * Time: 10:55
 */

namespace ScayTrase\Api\JsonRpc;

final class JsonRpcNotification implements JsonRpcRequestInterface
{
    /** @var  string */
    private $method;
    /** @var  \stdClass|array|null */
    private $parameters;

    /**
     * JsonRpcNotificationRequest constructor.
     * @param string $method
     * @param \stdClass|array|null $parameters
     */
    public function __construct($method, $parameters)
    {
        $this->method = $method;
        $this->parameters = $parameters;
    }

    /** {@inheritdoc} */
    public function getId()
    {
        return null;
    }

    /** {@inheritdoc} */
    public function isNotification()
    {
        return true;
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

    /** {@inheritdoc} */
    public function getVersion()
    {
        return JsonRpcClient::VERSION;
    }

    /** {@inheritdoc} */
    public function jsonSerialize()
    {
        return [
            self::VERSION_FIELD => JsonRpcClient::VERSION,
            self::METHOD_FIELD => $this->getMethod(),
            self::PARAMETERS_FIELD => $this->getParameters(),
        ];
    }
}
