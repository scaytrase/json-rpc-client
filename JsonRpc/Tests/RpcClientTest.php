<?php

namespace ScayTrase\Api\JsonRpc\Tests;

use GuzzleHttp\Psr7\Uri;
use Prophecy\Argument;
use ScayTrase\Api\IdGenerator\IdGeneratorInterface;
use ScayTrase\Api\JsonRpc\JsonRpcClient;
use ScayTrase\Api\Rpc\Tests\RpcRequestTrait;

final class RpcClientTest extends AbstractJsonRpcClientTest
{
    use RpcRequestTrait;

    const REQUEST_ID = 'request_id';

    public function testRpcRequestHandling()
    {
        $request = $this->getRequestMock('test', ['param1' => 'value1']);
        $client = new JsonRpcClient($this->getClient(), new Uri(), $this->getStaticIdGenerator(self::REQUEST_ID));

        $this->pushResult((object)['success' => true], self::REQUEST_ID);

        $collection = $client->invoke($request);
        $response = $collection->getResponse($request);
    }

    public function getStaticIdGenerator($id)
    {
        $generator = $this->prophesize(IdGeneratorInterface::class);
        $generator->getRequestIdentifier(Argument::any())->willReturn($id);

        return $generator->reveal();
    }
}
