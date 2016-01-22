<?php
/**
 * User: scaytrase
 * Date: 2016-01-04
 * Time: 11:55
 */

namespace ScayTrase\Api\JsonRpc;

use ScayTrase\Api\JsonRpc\Exception\ResponseParseException;

/** @internal */
final class ResponseBodyValidator
{
    /**
     * @param \StdClass $response
     * @throws ResponseParseException
     */
    public function validate(\StdClass $response)
    {
        if (property_exists($response, JsonRpcResponseInterface::ERROR_FIELD) && property_exists($response, JsonRpcResponseInterface::RESULT_FIELD)) {
            throw ResponseParseException::bothErrorAndResultPresent();
        }

        if (!property_exists($response, JsonRpcResponseInterface::ERROR_FIELD) && !property_exists($response, JsonRpcResponseInterface::RESULT_FIELD)) {
            throw ResponseParseException::noErrorOrResultPresent();
        }

        if (!property_exists($response, JsonRpcResponseInterface::ID_FIELD)) {
            throw ResponseParseException::noIdSpecified();
        }

        if (!property_exists($response, JsonRpcResponseInterface::VERSION_FIELD)) {
            throw ResponseParseException::noVersionSpecified();
        }

        if ($response->jsonrpc !== JsonRpcClient::VERSION) {
            throw ResponseParseException::inconsistentVersionReceived();
        }

        if (property_exists($response, JsonRpcResponseInterface::ERROR_FIELD)) {
            if (!is_object($response->{JsonRpcResponseInterface::ERROR_FIELD})) {
                throw ResponseParseException::errorIsNotAnObject();
            }

            if (!property_exists($response->{JsonRpcResponseInterface::ERROR_FIELD}, JsonRpcResponseInterface::ERROR_CODE_FIELD)) {
                throw ResponseParseException::noErrorCodePresent();
            }
            if (!property_exists($response->{JsonRpcResponseInterface::ERROR_FIELD}, JsonRpcResponseInterface::ERROR_MESSAGE_FIELD)) {
                throw ResponseParseException::noErrorMessagePresent();
            }

        }
    }
}
