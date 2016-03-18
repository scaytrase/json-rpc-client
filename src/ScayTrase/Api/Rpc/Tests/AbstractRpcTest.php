<?php
/**
 * Created by PhpStorm.
 * User: batanov.pavel
 * Date: 18.03.2016
 * Time: 15:56
 */

namespace ScayTrase\Api\Rpc\Tests;

use ScayTrase\Api\Rpc\ResponseCollectionInterface;
use ScayTrase\Api\Rpc\RpcClientInterface;
use ScayTrase\Api\Rpc\RpcRequestInterface;
use ScayTrase\Api\Rpc\RpcResponseInterface;

abstract class AbstractRpcTest extends \PHPUnit_Framework_TestCase
{
    private $collectionCallbacks = [];

    /**
     * @param string $method
     * @param array  $params
     *
     * @return RpcRequestInterface
     */
    protected function getRequestMock($method, array $params = [])
    {
        $mock = self::getMock(RpcRequestInterface::class);
        $mock->method('getMethod')->willReturn($method);
        $mock->method('getParameters')->willReturn((object)$params);

        return $mock;
    }

    /**
     * @param array $data
     *
     * @return RpcResponseInterface
     */
    protected function getResponseMock(array $data = [])
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
    protected function getCollectionMock(
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

    /**
     * @param $requests
     * @param $collection
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|RpcClientInterface
     */
    protected function getClientMock($requests, $collection)
    {
        /** @var RpcClientInterface|\PHPUnit_Framework_MockObject_MockObject $client */
        $client = $this->getMock(RpcClientInterface::class);
        $client->expects(self::once())
               ->method('invoke')
               ->with($requests)
               ->willReturn($collection);

        return $client;
    }
}
