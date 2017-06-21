<?php

namespace ScayTrase\Api\JsonRpc\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Uri;
use ScayTrase\Api\JsonRpc\JsonRpcClient;
use ScayTrase\Api\JsonRpc\JsonRpcError;
use ScayTrase\Api\JsonRpc\JsonRpcErrorInterface;
use ScayTrase\Api\JsonRpc\JsonRpcNotification;
use ScayTrase\Api\JsonRpc\JsonRpcRequest;
use ScayTrase\Api\JsonRpc\JsonRpcResponseInterface;
use ScayTrase\Api\Rpc\Exception\RemoteCallFailedException;
use ScayTrase\Api\Rpc\RpcErrorInterface;

final class JsonRpcClientTest extends AbstractJsonRpcClientTest
{
    public function paramsProvider()
    {
        return [
            'Null' => [null],
            'Empty array' => [[]],
            'Scalar' => [5], // This really should be an exception
            'Named Params' => [(object)['test' => 'test']],
            'Positional params' => [['test']],
        ];
    }

    /**
     * @dataProvider paramsProvider
     * @param $params
     */
    public function testSingleRequestFormatting($params)
    {
        $client = $this->getProphetClient('test', $params, false);
        $client->invoke(new JsonRpcRequest('test', $params, 'test'));
    }

    /**
     * @dataProvider paramsProvider
     * @param $params
     */
    public function testBatchRequestFormatting($params)
    {
        $client = $this->getProphetClient('test', $params, true);
        $client->invoke([new JsonRpcRequest('test', $params, 'test')]);
    }

    public function testSingleSuccessfulRequest()
    {
        $client = new JsonRpcClient($this->getClient(), new Uri('http://localhost/'));

        $request = $this->createRequestForSingleInvocation('/test', ['parameter' => 'test'], ['foo' => 'bar']);
        $collection = $client->invoke([$request]);

        /** @var JsonRpcResponseInterface $response */
        $response = $collection->getResponse($request);
        self::assertTrue($response->isSuccessful());
        self::assertNotNull($response->getBody());
        self::assertEquals($client::VERSION, $response->getVersion());
        self::assertNull($response->getError());
        self::assertObjectHasAttribute('foo', $response->getBody());
        self::assertEquals('bar', $response->getBody()->foo);
    }

    public function testSingleFailedRequest()
    {
        $client = new JsonRpcClient($this->getClient(), new Uri('http://localhost/'));

        $request = $this->createRequestForSingleInvocation(
            '/test',
            ['parameter' => 'test'],
            new JsonRpcError(JsonRpcErrorInterface::INTERNAL_ERROR, 'Test error')
        );
        $collection = $client->invoke([$request]);

        /** @var JsonRpcResponseInterface $response */
        $response = $collection->getResponse($request);
        self::assertFalse($response->isSuccessful());
        self::assertNull($response->getBody());
        self::assertNotNull($response->getError());
        self::assertEquals($client::VERSION, $response->getVersion());
        self::assertInstanceOf(RpcErrorInterface::class, $response->getError());
        self::assertEquals(JsonRpcErrorInterface::INTERNAL_ERROR, $response->getError()->getCode());
        self::assertEquals('Test error', $response->getError()->getMessage());
    }

    public function testSingleNotification()
    {
        $client = new JsonRpcClient($this->getClient(), new Uri('http://localhost/'));

        $notification = $this->createNotificationForSingleInvocation('/test-notify', ['parameter' => 'test']);
        $collection = $client->invoke([$notification]);

        $response = $collection->getResponse($notification);
        self::assertTrue($response->isSuccessful());
        self::assertNull($response->getBody());
        self::assertNull($response->getError());
        self::assertEquals($client::VERSION, $response->getVersion());
    }

    public function testMultipleMixedRequests()
    {
        $handler = HandlerStack::create($this->getQueue());
        $this->client = new Client(['handler' => $handler]);

        $client = new JsonRpcClient($this->getClient(), new Uri('http://localhost/'));

        $request = new JsonRpcRequest('/test-request', [], $this->getRandomHash());
        $notification = new JsonRpcNotification('/test-notify', []);
        $error = new JsonRpcRequest('/test-error', [], $this->getRandomHash());

        $this->getQueue()->append(
            new Response(
                200, [], json_encode(
                [
                    ['jsonrpc' => '2.0', 'id' => $request->getId(), 'result' => ['success' => true]],
                    [
                        'jsonrpc' => '2.0',
                        'id' => $error->getId(),
                        'error' => ['code' => JsonRpcErrorInterface::INTERNAL_ERROR, 'message' => 'Test error'],
                    ],
                ]
            )
            )
        );

        $calls = [$request, $notification, $error];
        $collection = $client->invoke($calls);

        $rResponse = $collection->getResponse($request);
        $nResponse = $collection->getResponse($notification);
        $eResponse = $collection->getResponse($error);

        self::assertNotNull($rResponse);
        self::assertInstanceOf(JsonRpcResponseInterface::class, $rResponse);
        self::assertTrue($rResponse->isSuccessful());
        self::assertObjectHasAttribute('success', $rResponse->getBody());
        self::assertTrue($rResponse->getBody()->success);

        self::assertNotNull($nResponse);
        self::assertInstanceOf(JsonRpcResponseInterface::class, $nResponse);
        self::assertTrue($nResponse->isSuccessful());

        self::assertNotNull($eResponse);
        self::assertInstanceOf(JsonRpcResponseInterface::class, $eResponse);
        self::assertFalse($eResponse->isSuccessful());
        self::assertInstanceOf(JsonRpcErrorInterface::class, $eResponse->getError());
        self::assertEquals('Test error', $eResponse->getError()->getMessage());
        self::assertEquals(JsonRpcErrorInterface::INTERNAL_ERROR, $eResponse->getError()->getCode());
    }

    /** @expectedException \ScayTrase\Api\Rpc\Exception\RemoteCallFailedException */
    public function testFailedCall()
    {
        $client = new JsonRpcClient($this->getClient(), new Uri('http://localhost/'));

        $request = $this->createRequestForSingleInvocation(
            '/test-notify',
            ['parameter' => 'test'],
            new ConnectException('Connection failed', new Request('POST', 'test'))
        );

        $client->invoke([$request])->getResponse($request);
    }
}
