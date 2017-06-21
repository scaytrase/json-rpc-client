<?php

namespace ScayTrase\Api\JsonRpc\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Uri;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Http\Message\RequestInterface;
use ScayTrase\Api\JsonRpc\JsonRpcClient;
use ScayTrase\Api\JsonRpc\JsonRpcNotification;
use ScayTrase\Api\JsonRpc\JsonRpcRequest;
use ScayTrase\Api\Rpc\RpcErrorInterface;

abstract class AbstractJsonRpcClientTest extends TestCase
{
    /** @var  MockHandler */
    protected $queue;
    /** @var  ClientInterface */
    protected $client;

    protected function setUp()
    {
        $this->queue = null;
        $this->client = null;
        parent::setUp();
    }

    protected function tearDown()
    {
        if (null !== $this->queue) {
            self::assertEquals(0, $this->getQueue()->count());
        }
        parent::tearDown();
    }

    /** @return MockHandler */
    protected function getQueue()
    {
        if (null === $this->queue) {
            $this->queue = new MockHandler();
        }

        return $this->queue;
    }

    /** @return ClientInterface */
    protected function getClient()
    {
        if (null === $this->client) {
            $handler = HandlerStack::create($this->getQueue());
            $this->client = new Client(['handler' => $handler]);
        }

        return $this->client;
    }

    protected function createRequestForSingleInvocation($method, array $parameters, $result)
    {
        $hash = $this->getRandomHash();

        $this->pushResult($result, $hash);

        return new JsonRpcRequest($method, $parameters, $hash);
    }

    /**
     * @return string
     */
    protected function getRandomHash()
    {
        return bin2hex(random_bytes(20));
    }

    protected function createNotificationForSingleInvocation($method, array $parameters)
    {
        $notification = new JsonRpcNotification($method, $parameters);
        $this->getQueue()->append(new Response(200, [], null));

        return $notification;
    }

    /**
     * @param string $method
     * @param mixed $params
     * @param bool $batch
     * @return JsonRpcClient
     */
    protected function getProphetClient($method, $params, $batch)
    {
        $guzzle = $this->prophesize(ClientInterface::class);
        $self = $this;
        $guzzle->sendAsync(Argument::type(RequestInterface::class))->will(
            function ($args) use ($method, $self, $params, $batch) {
                /** @var RequestInterface $request */
                $request = $args[0];
                $content = $request->getBody()->getContents();
                $data = json_decode($content);
                if ($batch) {
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
                $self::assertEquals($method, $data->method);
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
     * @param $result
     * @param $hash
     */
    protected function pushResult($result, $hash = null)
    {
        if ($result instanceof GuzzleException) {
            $this->getQueue()->append($result);
        } elseif ($result instanceof RpcErrorInterface) {
            $this->getQueue()->append(
                new Response(
                    200,
                    [],
                    json_encode(
                        [
                            [
                                'jsonrpc' => '2.0',
                                'id' => $hash,
                                'error' => [
                                    'code' => $result->getCode(),
                                    'message' => $result->getMessage(),
                                ],
                            ],
                        ]
                    )
                )
            );
        } else {
            $this->getQueue()->append(
                new Response(
                    200,
                    [],
                    json_encode(
                        [
                            [
                                'jsonrpc' => '2.0',
                                'id' => $hash,
                                'result' => $result,
                            ],
                        ]
                    )
                )
            );
        }
    }
}
