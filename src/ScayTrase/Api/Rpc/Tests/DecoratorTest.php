<?php
/**
 * Created by PhpStorm.
 * User: batanov.pavel
 * Date: 18.03.2016
 * Time: 15:54
 */

namespace ScayTrase\Api\Rpc\Tests;

use Psr\Log\AbstractLogger;
use ScayTrase\Api\Rpc\Decorators\LoggableRpcClient;
use ScayTrase\Api\Rpc\RpcRequestInterface;

class DecoratorTest extends AbstractRpcTest
{
    public function testLoggableClient()
    {
//        if (!class_exists(LoggerInterface::class, false)) {
//            self::markTestSkipped('install psr/log in order to run these tests');
//        }

        $logger = self::getMock(AbstractLogger::class, ['log']);
        $logger->expects(self::atLeastOnce())->method('log');

        $rq1 = $this->getRequestMock('/test1', ['param1' => 'test']);
        $rs1 = $this->getResponseMock(['param1' => 'test']);
        /** @var RpcRequestInterface[] $requests */
        $requests   = [$rq1];
        $collection = $this->getCollectionMock($rq1, $rs1, null);

        $client = new LoggableRpcClient($this->getClientMock($requests, $collection), $logger);

        self::assertEquals($rs1, $client->invoke($requests)->getResponse($rq1));
    }

    public function testCacheableClient()
    {
//        if (!class_exists(CacheItemPoolInterface::class, false)) {
//            self::markTestSkipped('install psr/cache in order to run these tests');
//        }

        $rq1 = $this->getRequestMock('/test1', ['param1' => 'test']);
        $rq2 = $this->getRequestMock('/test2', ['param2' => 'test']);
        $rq3 = $this->getRequestMock('/test1', ['param1' => 'test']);

        self::markTestIncomplete('Not implemented yet');
    }
}
