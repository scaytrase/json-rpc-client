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
    /** @var  array */
    private $parameters;

    /**
     * JsonRpcNotificationRequest constructor.
     * @param string $method
     * @param array $parameters
     */
    public function __construct($method, array $parameters)
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
}
