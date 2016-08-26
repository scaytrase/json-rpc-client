<?php
/**
 * User: scaytrase
 * Date: 2016-01-03
 * Time: 22:16
 */

namespace ScayTrase\Api\JsonRpc\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Uri;
use Prophecy\Argument;
use Psr\Http\Message\RequestInterface;
use ScayTrase\Api\JsonRpc\JsonRpcClient;
use ScayTrase\Api\JsonRpc\JsonRpcError;
use ScayTrase\Api\JsonRpc\JsonRpcErrorInterface;
use ScayTrase\Api\JsonRpc\JsonRpcNotification;
use ScayTrase\Api\JsonRpc\JsonRpcRequest;
use ScayTrase\Api\JsonRpc\JsonRpcResponseInterface;
use ScayTrase\Api\Rpc\RpcErrorInterface;

class JsonRpcClientTest extends AbstractJsonRpcClientTest
{
    public function paramsProvider()
    {
        return [
            'Null' => [null],
            'Empty array' => [[]],
            'Scalar' => [5], // This really shoud be an exception
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
        $client = $this->getProphetClient($params, false);
        $client->invoke(new JsonRpcRequest('test', $params, 'test'));
    }

    /**
     * @param mixed $params
     * @param bool $isArray
     * @return JsonRpcClient
     */
    private function getProphetClient($params, $isArray)
    {
        $guzzle = $this->prophesize(ClientInterface::class);
        $self = $this;
        $guzzle->sendAsync(Argument::type(RequestInterface::class))->will(
            function ($args) use ($self, $params, $isArray) {
                /** @var RequestInterface $request */
                $request = $args[0];
                $content = $request->getBody()->getContents();
                $data = json_decode($content);
                if ($isArray) {
                    $self::assertTrue(is_array($data));
                    $self::assertNotEmpty($data);
                    $data = array_shift($data);
                }
                $self::assertEquals(JSON_ERROR_NONE, json_last_error());
                $self::assertObjectHasAttribute('id', $data);
                $self::assertObjectHasAttribute('method', $data);
                $self::assertObjectHasAttribute('params', $data);
                $self::assertObjectHasAttribute('jsonrpc', $data);
                $self::assertEquals('test', $data->id);
                $self::assertEquals('2.0', $data->jsonrpc);
                $self::assertEquals('test', $data->method);
                $self::assertEquals($params, $data->params);

                return new Promise(
                    function () {
                    },
                    function () {
                    }
                );
            }
        );

        return new JsonRpcClient($guzzle->reveal(), new Uri('http://localhost/'));
    }

    /**
     * @dataProvider paramsProvider
     * @param $params
     */
    public function testBatchRequestFormatting($params)
    {
        $client = $this->getProphetClient($params, true);
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

        $request = $this->createRequestForSingleInvocation('/test', ['parameter' => 'test'], new JsonRpcError(JsonRpcErrorInterface::INTERNAL_ERROR, 'Test error'));
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

        $this->getQueue()->append(new Response(200, [], json_encode(
            [
                ['jsonrpc' => '2.0', 'id' => $request->getId(), 'result' => ['success' => true]],
                ['jsonrpc' => '2.0', 'id' => $error->getId(), 'error' => ['code' => JsonRpcErrorInterface::INTERNAL_ERROR, 'message' => 'Test error']],
            ]
        )));

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

    /** @expectedException \GuzzleHttp\Exception\GuzzleException */
    public function testFailedCall()
    {
        $client = new JsonRpcClient($this->getClient(), new Uri('http://localhost/'));

        $request = $this->createRequestForSingleInvocation('/test-notify', ['parameter' => 'test'], new ConnectException('Connection failed', new Request('POST', 'test')));

        $client->invoke([$request])->getResponse($request);
    }
}
