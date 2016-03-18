<?php
/**
 * Created by PhpStorm.
 * User: batanov.pavel
 * Date: 18.03.2016
 * Time: 15:56
 */

namespace ScayTrase\Api\Rpc\Tests;

use Prophecy\Argument;
use ScayTrase\Api\Rpc\ResponseCollectionInterface;
use ScayTrase\Api\Rpc\RpcClientInterface;
use ScayTrase\Api\Rpc\RpcRequestInterface;
use ScayTrase\Api\Rpc\RpcResponseInterface;

abstract class AbstractRpcTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $method
     * @param array  $params
     *
     * @return RpcRequestInterface
     */
    protected function getRequestMock($method, array $params = [])
    {
        $request = $this->prophesize(RpcRequestInterface::class);
        $request->getMethod()->willReturn($method);
        $request->getParameters()->willReturn((object)$params);


        return $request->reveal();
    }

    /**
     * @param array $data
     *
     * @return RpcResponseInterface
     */
    protected function getResponseMock(array $data = [])
    {
        $response = $this->prophesize(RpcResponseInterface::class);
        $response->isSuccessful()->willReturn(true);
        $response->getBody()->willReturn((object)$data);

        return $response->reveal();
    }

    /**
     * @param RpcRequestInterface[] $requests
     * @param RpcResponseInterface[] $responses
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|RpcClientInterface
     */
    protected function getClientMock(array $requests = [], array $responses = [])
    {
        self::assertEquals(count($requests), count($responses));

        $client = $this->prophesize(RpcClientInterface::class);
        $that = $this;
        $client->invoke(Argument::type('array'))->will(function ($args) use ($that, $requests, $responses) {
            $collection = $that->prophesize(ResponseCollectionInterface::class);
            foreach ($requests as $key => $request) {
                if (in_array($request, $args[0], true)) {
                    $collection->getResponse(Argument::exact($request))->willReturn($responses[$key]);
                }
            }
            return $collection->reveal();
        });

        return $client->reveal();
    }
}
