<?php

namespace ScayTrase\Api\Rpc\Tests;

use ScayTrase\Api\Rpc\LazyRpcClient;
use ScayTrase\Api\Rpc\ResponseCollectionInterface;
use ScayTrase\Api\Rpc\RpcClientInterface;
use ScayTrase\Api\Rpc\RpcRequestInterface;
use ScayTrase\Api\Rpc\RpcResponseInterface;

/**
 * Created by PhpStorm.
 * User: batanov.pavel
 * Date: 08.02.2016
 * Time: 10:55
 */
class LazyClientTest extends \PHPUnit_Framework_TestCase
{
    private $collectionCallbacks = [];

    public function testLazyRequets()
    {
        $rq1 = $this->getRequestMock('/test1', ['param1' => 'test']);
        $rq2 = $this->getRequestMock('/test2', ['param2' => 'test']);
        $rq3 = $this->getRequestMock('/test3', ['param3' => 'test']);

        $rs1 = $this->getResponseMock(['param1' => 'test']);
        $rs2 = $this->getResponseMock(['param2' => 'test']);
        $rs3 = $this->getResponseMock(['param3' => 'test']);

        /** @var RpcRequestInterface[] $requests */
        $requests = [$rq1, $rq2, $rq3];

        $collection = null;
        $collection = $this->getCollectionMock($rq1, $rs1, $collection);
        $collection = $this->getCollectionMock($rq2, $rs2, $collection);
        $collection = $this->getCollectionMock($rq3, $rs3, $collection);

        /** @var RpcClientInterface|\PHPUnit_Framework_MockObject_MockObject $client */
        $client = $this->getMock(RpcClientInterface::class);
        $client->expects(self::once())->method('invoke')->with($requests)->willReturn($collection);

        $lazyClient = new LazyRpcClient($client);

        $c1 = $lazyClient->invoke($rq1);
        $c2 = $lazyClient->invoke($rq2);
        $c3 = $lazyClient->invoke($rq3);

        self::assertEquals($c1, $c2);
        self::assertEquals($c1, $c3);


        /** @var RpcResponseInterface[] $responses */
        $responses = [$rs1, $rs2, $rs3];

        foreach ($requests as $id => $request) {

            $response = $c1->getResponse($request);

            self::assertNotNull($response);
            self::assertTrue($c1->isFrozen());
            self::assertEquals($response, $responses[$id]);
            self::assertTrue($response->isSuccessful());
            self::assertInstanceOf(\StdClass::class, $response->getBody());
            self::assertEquals($request->getParameters(), (array)$response->getBody());
        }
    }

    /**
     * @param string $method
     * @param array  $params
     *
     * @return RpcRequestInterface
     */
    private function getRequestMock($method, array $params = [])
    {
        $mock = self::getMock(RpcRequestInterface::class);
        $mock->method('getMethod')->willReturn($method);
        $mock->method('getParameters')->willReturn($params);

        return $mock;
    }

    /**
     * @param array $data
     *
     * @return RpcResponseInterface
     */
    private function getResponseMock(array $data = [])
    {
        $responseMock = self::getMock(RpcResponseInterface::class);
        $responseMock->method('isSuccessful')->willReturn(true);
        $responseMock->method('getBody')->willReturn((object)$data);

        return $responseMock;
    }

    /**
     * @param RpcRequestInterface                                                       $request
     * @param \PHPUnit_Framework_MockObject_MockObject|null $collection
     * @param RpcResponseInterface                                                      $response
     *
     * @return ResponseCollectionInterface
     */
    private function getCollectionMock(
        RpcRequestInterface $request,
        RpcResponseInterface $response,
        $collection = null
    )
    {
        if (null === $collection) {
            $collection                                              =
                self::getMock(ResponseCollectionInterface::class);
            $this->collectionCallbacks[spl_object_hash($collection)] = [];
        }

        // Hack to make Mock return different responses with different parameters
        $this->collectionCallbacks[spl_object_hash($collection)][spl_object_hash($request)] = $response;

        $collection->method('getResponse')->willReturnCallback(
            function (RpcRequestInterface $request) use ($collection) {
                return $this->collectionCallbacks[spl_object_hash($collection)][spl_object_hash($request)];
            }
        );

        return $collection;
    }
}
