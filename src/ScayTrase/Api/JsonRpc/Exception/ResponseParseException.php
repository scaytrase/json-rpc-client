<?php
/**
 * User: scaytrase
 * Date: 2016-01-03
 * Time: 13:13
 */

namespace ScayTrase\Api\JsonRpc\Exception;

class ResponseParseException extends \RuntimeException implements JsonRpcExceptionInterface
{
    /** @return static */
    public static function noErrorOrResultPresent()
    {
        return new static(sprintf(
            'Response received, but no error or result field present'
        ));
    }

    /** @return static */
    public static function bothErrorAndResultPresent()
    {
        return new static(sprintf(
            'Response received, but both result and error fields are present'
        ));
    }

    /** @return static */
    public static function noErrorCodePresent()
    {
        return new static(sprintf(
            'Response received, error present, but no code field specified'
        ));
    }

    /** @return static */
    public static function noErrorMessagePresent()
    {
        return new static(sprintf(
            'Response received, error present, but no message field specified'
        ));
    }

    /** @return static */
    public static function noVersionSpecified()
    {
        return new static(sprintf(
            'Response received, but no JSON-RPC version specified'
        ));
    }

    /** @return static */
    public static function inconsistentVersionReceived()
    {
        return new static(sprintf(
            'Response received, but response JSON-RPC version does not match client JSON-RPC version'
        ));
    }

    /** @return static */
    public static function notAJsonResponse()
    {
        return new static(sprintf(
            'Response received, but response body is not a valid JSON object'
        ));
    }

    /** @return static */
    public static function noIdSpecified()
    {
        return new static(sprintf(
            'Response received, but no JSON-RPC Request ID specified'
        ));
    }

    /**
     * @return static
     */
    public static function errorIsNotAnObject()
    {
        return new static(sprintf(
            'Response received, but error has invalid format'
        ));
    }
}
