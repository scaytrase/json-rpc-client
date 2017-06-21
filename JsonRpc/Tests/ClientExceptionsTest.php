<?php

namespace ScayTrase\Api\JsonRpc\Tests;

use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use ScayTrase\Api\JsonRpc\JsonRpcClient;
use ScayTrase\Api\Rpc\Exception\RemoteCallFailedException;

class ClientExceptionsTest extends AbstractJsonRpcClientTest
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Request should be either array or single RpcRequestInterface instance
     */
    public function testInvalidArrayArgumentException()
    {
        $client = new JsonRpcClient($this->getClient(), new Uri());
        $client->invoke('test');
    }

    /**
     * @expectedException \ScayTrase\Api\Rpc\Exception\RemoteCallFailedException
     * @expectedExceptionMessage Error completing request
     */
    public function testGuzzleException()
    {
        $client = new JsonRpcClient($this->getClient(), new Uri());
        $exception = ServerException::create(new Request('GET', new Uri()));
        $request = $this->createRequestForSingleInvocation('test', [], $exception);
        $client->invoke($request)->getResponse($request);
    }
}
