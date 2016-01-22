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
    /** @var null|\StdClass */
    private $data;

    /**
     * JsonRpcError constructor.
     * @param int $code
     * @param string $message
     * @param \StdClass|null $data
     */
    public function __construct($code, $message, \StdClass $data = null)
    {
        $this->code = $code;
        $this->message = $message;
        $this->data = $data;
    }

    /** @return int */
    public function getCode()
    {
        return $this->code;
    }

    /** @return string */
    public function getMessage()
    {
        return $this->message;
    }

    /** @return \StdClass|null error data */
    public function getData()
    {
        return $this->data;
    }
}
