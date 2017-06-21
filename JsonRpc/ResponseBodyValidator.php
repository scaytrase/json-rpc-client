<?php

namespace ScayTrase\Api\JsonRpc;

use ScayTrase\Api\JsonRpc\Exception\ResponseParseException;

/** @internal */
final class ResponseBodyValidator
{
    /**
     * @param \stdClass $response
     * @throws ResponseParseException
     */
    public function validate(\stdClass $response)
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

            if (!property_exists($response->{JsonRpcResponseInterface::ERROR_FIELD}, JsonRpcErrorInterface::ERROR_CODE_FIELD)) {
                throw ResponseParseException::noErrorCodePresent();
            }

            $error = $response->{JsonRpcResponseInterface::ERROR_FIELD}->{JsonRpcErrorInterface::ERROR_CODE_FIELD};
            if (!is_int($error)) {
                throw ResponseParseException::unexpectedType('error.code', 'integer', gettype($error));
            }

            if (!property_exists($response->{JsonRpcResponseInterface::ERROR_FIELD}, JsonRpcErrorInterface::ERROR_MESSAGE_FIELD)) {
                throw ResponseParseException::noErrorMessagePresent();
            }

            $message = $response->{JsonRpcResponseInterface::ERROR_FIELD}->{JsonRpcErrorInterface::ERROR_MESSAGE_FIELD};
            if (!is_string($message)) {
                throw ResponseParseException::unexpectedType('error.message', 'string', gettype($message));
            }
        }
    }
}
