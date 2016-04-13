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
    /** @var  \stdClass */
    private $response;
    /** @var  JsonRpcError */
    private $error;

    /**
     * SyncResponse constructor.
     * @param \stdClass $response
     * @throws ResponseParseException on creating response for notification
     */
    public function __construct(\stdClass $response)
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
        if (property_exists($rawError, JsonRpcErrorInterface::ERROR_DATA_FIELD)) {
            $data = $rawError->data;
        }

        $this->error = new JsonRpcError(
            $rawError->{JsonRpcErrorInterface::ERROR_CODE_FIELD},
            $rawError->{JsonRpcErrorInterface::ERROR_MESSAGE_FIELD},
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

    /** {@inheritdoc} */
    public function getVersion()
    {
        return $this->response->jsonrpc;
    }

    /** {@inheritdoc} */
    public function getId()
    {
        return $this->response->id;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        $result = [
            self::VERSION_FIELD => JsonRpcClient::VERSION,
            self::ID_FIELD => $this->getId(),
        ];

        if ($this->isSuccessful()) {
            $result[self::RESULT_FIELD] = $this->getBody();
        }

        if (!$this->isSuccessful()) {
            $result[self::ERROR_FIELD] = $this->getError();
        }

        return $result;
    }
}
