<?php

namespace ScayTrase\Api\JsonRpc\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
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
        $this->queue  = null;
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
            $handler      = HandlerStack::create($this->getQueue());
            $this->client = new Client(['handler' => $handler]);
        }

        return $this->client;
    }

    protected function createRequestForSingleInvocation($method, array $parameters, $result)
    {
        $hash = $this->getRandomHash();

        $request = new JsonRpcRequest($method, $parameters, $hash);

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
                                'id'      => $hash,
                                'error'   => [
                                    'code'    => $result->getCode(),
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
                                'id'      => $hash,
                                'result'  => $result,
                            ],
                        ]
                    )
                )
            );
        }

        return $request;
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
}
