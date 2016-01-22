<?php
/**
 * User: scaytrase
 * Date: 2016-01-04
 * Time: 13:13
 */

namespace ScayTrase\Api\JsonRpc\Tests;

use ScayTrase\Api\JsonRpc\JsonRpcErrorInterface;
use ScayTrase\Api\JsonRpc\ResponseBodyValidator;

class ResponseBodyValidatorTest extends \PHPUnit_Framework_TestCase
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
            'result and id and invalid version' => [(object)['jsonrpc' => '1.1', 'result' => (object)['success' => true], 'id' => 1234]],
            'both result and error present' => [(object)['jsonrpc' => '2.0', 'result' => (object)['success' => true], 'id' => 1234, 'error' => (object)['code' => JsonRpcErrorInterface::INTERNAL_ERROR, 'message' => 'Test error']]],
        ];
    }

    public function validResponseBodyProvider()
    {
        return [
            'valid response' => [(object)['jsonrpc' => '2.0', 'result' => ['success' => true], 'id' => 1234]],
            'valid empty response' => [(object)['jsonrpc' => '2.0', 'result' => null, 'id' => 1234]],
            'valid error' => [(object)['jsonrpc' => '2.0', 'error' => (object)['code' => JsonRpcErrorInterface::INTERNAL_ERROR, 'message' => 'Test error'], 'id' => 1234]],
            'valid error w\ data' => [(object)['jsonrpc' => '2.0', 'error' => (object)['code' => JsonRpcErrorInterface::INTERNAL_ERROR, 'message' => 'Test error', 'data' => 'Test error data'], 'id' => 1234]],
        ];
    }

    /**
     * @param \StdClass $body
     *
*@dataProvider invalidResponseBodyProvider
     * @expectedException \ScayTrase\Api\JsonRpc\Exception\ResponseParseException
     */
    public function testInvalidBody(\StdClass $body)
    {
        $parser = new ResponseBodyValidator();
        $parser->validate($body);
    }

    /**
     * @param \StdClass $body
     * @dataProvider validResponseBodyProvider
     */
    public function testValidBody(\StdClass $body)
    {
        $parser = new ResponseBodyValidator();
        $parser->validate($body);
    }

}
