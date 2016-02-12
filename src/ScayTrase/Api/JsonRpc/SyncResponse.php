<?php
/**
 * User: scaytrase
 * Date: 2016-01-02
 * Time: 22:15
 */

namespace ScayTrase\Api\JsonRpc;

use ScayTrase\Api\JsonRpc\Exception\ResponseParseException;

final class SyncResponse implements JsonRpcResponseInterface
{
    /** @var  \StdClass */
    private $response;
    /** @var  JsonRpcError */
    private $error;

    /**
     * SyncResponse constructor.
     * @param \StdClass $response
     * @throws ResponseParseException on creating response for notification
     */
    public function __construct(\StdClass $response)
    {
        $this->response = $response;

        /** @noinspection PhpInternalEntityUsedInspection */
        $validator = new ResponseBodyValidator();
        $validator->validate($this->response);
    }


    /** {@inheritdoc} */
    public function getError()
    {
        if ($this->isSuccessful()) {
            return null;
        }

        if (null !== $this->error) {
            return $this->error;
        }

        $rawError = $this->response->error;

        $data = null;
        if (property_exists($rawError, JsonRpcResponseInterface::ERROR_DATA_FIELD)) {
            $data = $rawError->data;
        }

        $this->error = new JsonRpcError(
            $rawError->{JsonRpcResponseInterface::ERROR_CODE_FIELD},
            $rawError->{JsonRpcResponseInterface::ERROR_MESSAGE_FIELD},
            $data
        );

        return $this->error;
    }

    /** {@inheritdoc} */
    public function isSuccessful()
    {
        return property_exists($this->response, JsonRpcResponseInterface::RESULT_FIELD) && !property_exists($this->response, JsonRpcResponseInterface::ERROR_FIELD);
    }

    /** {@inheritdoc} */
    public function getBody()
    {
        if (!$this->isSuccessful()) {
            return null;
        }

        return $this->response->result;
    }

    /**
     * @return string JSON-RPC version
     */
    public function getVersion()
    {
        return $this->response->jsonrpc;
    }

    /** @return string|null Response ID or null for notification pseudo-response */
    public function getId()
    {
        return $this->response->id;
    }
}
