<?php

namespace ScayTrase\Api\JsonRpc\Tests;

use PHPUnit\Framework\TestCase;
use ScayTrase\Api\JsonRpc\JsonRpcClient;
use ScayTrase\Api\JsonRpc\JsonRpcErrorInterface;
use ScayTrase\Api\JsonRpc\SyncResponse;

final class ResponseBodyValidatorTest extends TestCase
{
    public function invalidResponseBodyProvider()
    {
        return [
            'empty body' => [(object)null],
            'only version' => [(object)['jsonrpc' => '2.0']],
            'invalid version' => [(object)['jsonrpc' => '1.1']],
            'only result' => [(object)['result' => (object)['success' => true]]],
            'only id' => [(object)['id' => 1234]],
            'result and id' => [(object)['result' => (object)['success' => true], 'id' => 1234]],
            'result and id and invalid version' => [
                (object)[
                    'jsonrpc' => '1.1',
                    'result' => (object)['success' => true],
                    'id' => 1234,
                ],
            ],
            'both result and error present' => [
                (object)[
                    'jsonrpc' => '2.0',
                    'result' => (object)['success' => true],
                    'id' => 1234,
                    'error' => (object)[
                        'code' => JsonRpcErrorInterface::INTERNAL_ERROR,
                        'message' => 'Test error',
                    ],
                ],
            ],
            'error is not an object' => [
                (object)[
                    'jsonrpc' => '2.0',
                    'id' => 1234,
                    'error' => [
                        JsonRpcErrorInterface::INTERNAL_ERROR,
                        'Test error',
                    ],
                ],
            ],
            'no error code' => [
                (object)[
                    'jsonrpc' => '2.0',
                    'id' => 1234,
                    'error' => (object)[
                        'message' => 'Test error',
                    ],
                ],
            ],
            'code is not an int' => [
                (object)[
                    'jsonrpc' => '2.0',
                    'id' => 1234,
                    'error' => (object)[
                        'code' => 'string',
                    ],
                ],
            ],
            'no error message' => [
                (object)[
                    'jsonrpc' => '2.0',
                    'id' => 1234,
                    'error' => (object)[
                        'code' => JsonRpcErrorInterface::INTERNAL_ERROR,
                    ],
                ],
            ],
            'message is not a string' => [
                (object)[
                    'jsonrpc' => '2.0',
                    'id' => 1234,
                    'error' => (object)[
                        'code' => 1,
                        'message' => [],
                    ],
                ],
            ],
        ];
    }

    public function validResponseBodyProvider()
    {
        return [
            'valid response' => [(object)['jsonrpc' => '2.0', 'result' => (object)['success' => true], 'id' => 1234]],
            'valid empty response' => [(object)['jsonrpc' => '2.0', 'result' => null, 'id' => 1234]],
            'valid error' => [
                (object)[
                    'jsonrpc' => '2.0',
                    'error' => (object)[
                        'code' => JsonRpcErrorInterface::INTERNAL_ERROR,
                        'message' => 'Test error',
                    ],
                    'id' => 1234,
                ],
            ],
            'valid error w\ data' => [
                (object)[
                    'jsonrpc' => '2.0',
                    'error' => (object)[
                        'code' => JsonRpcErrorInterface::INTERNAL_ERROR,
                        'message' => 'Test error',
                        'data' => 'Test error data',
                    ],
                    'id' => 1234,
                ],
            ],
        ];
    }

    /**
     * @param \stdClass $body
     *
     * @dataProvider invalidResponseBodyProvider
     * @expectedException \ScayTrase\Api\JsonRpc\Exception\ResponseParseException
     */
    public function testInvalidBody(\stdClass $body)
    {
        new SyncResponse($body);
    }

    /**
     * @param \stdClass $body
     *
     * @dataProvider validResponseBodyProvider
     */
    public function testValidBody(\stdClass $body)
    {
        $response = new SyncResponse($body);
        self::assertEquals(JsonRpcClient::VERSION, $response->getVersion());
        self::assertEquals(json_decode(json_encode($response)), $body);
    }
}
