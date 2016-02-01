<?php
/**
 * User: scaytrase
 * Date: 2016-01-03
 * Time: 23:22
 */

namespace ScayTrase\Api\JsonRpc;

final class JsonRpcNotificationResponse implements JsonRpcResponseInterface
{
    /** {@inheritdoc} */
    public function getResult()
    {
        return null;
    }

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
    public function getHeaders()
    {
        return [];
    }

    /** {@inheritdoc} */
    public function getId()
    {
        return null;
    }
}
