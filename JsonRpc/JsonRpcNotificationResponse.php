<?php

namespace ScayTrase\Api\JsonRpc;

final class JsonRpcNotificationResponse implements JsonRpcResponseInterface
{
    /** {@inheritdoc} */
    public function getVersion()
    {
        return JsonRpcClient::VERSION;
    }

    /** {@inheritdoc} */
    public function isSuccessful()
    {
        return true;
    }

    /** {@inheritdoc} */
    public function getError()
    {
        return null;
    }

    /** {@inheritdoc} */
    public function getBody()
    {
        return null;
    }

    /** {@inheritdoc} */
    public function getId()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     * @throws \LogicException
     */
    public function jsonSerialize()
    {
        throw new \LogicException('Notification should not have transferable response representation');
    }
}
