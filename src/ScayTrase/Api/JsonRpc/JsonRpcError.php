<?php
/**
 * User: scaytrase
 * Date: 2016-01-03
 * Time: 10:46
 */

namespace ScayTrase\Api\JsonRpc;

final class JsonRpcError implements JsonRpcErrorInterface
{
    /** @var int */
    private $code;
    /** @var string */
    private $message;
    /** @var null|\stdClass */
    private $data;

    /**
     * JsonRpcError constructor.
     * @param int $code
     * @param string $message
     * @param \stdClass|mixed|null $data
     */
    public function __construct($code, $message, $data = null)
    {
        $this->code = $code;
        $this->message = $message;
        $this->data = $data;
    }

    /** {@inheritdoc} */
    public function getCode()
    {
        return $this->code;
    }

    /** {@inheritdoc} */
    public function getMessage()
    {
        return $this->message;
    }

    /** {@inheritdoc} */
    public function getData()
    {
        return $this->data;
    }

    /** {@inheritdoc} */
    public function jsonSerialize()
    {
        $error = [
            self::ERROR_CODE_FIELD => $this->getCode(),
            self::ERROR_MESSAGE_FIELD => $this->getMessage(),
        ];

        if (null !== ($data = $this->getData())) {
            $error[self::ERROR_DATA_FIELD] = $data;
        }

        return $error;
    }
}
