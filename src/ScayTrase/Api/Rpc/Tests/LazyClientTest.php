<?php

namespace ScayTrase\Api\Rpc\Tests;

use ScayTrase\Api\Rpc\Decorators\LazyRpcClient;
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
class LazyClientTest extends AbstractRpcTest
{
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
        $client     = $this->getClientMock($requests, $collection);


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
            self::assertEquals($request->getParameters(), $response->getBody());
        }
    }
}
