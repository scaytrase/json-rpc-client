<?php
/**
 * Created by PhpStorm.
 * User: batanov.pavel
 * Date: 18.03.2016
 * Time: 15:54
 */

namespace ScayTrase\Api\Rpc\Tests;

use Prophecy\Argument;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;
use ScayTrase\Api\Rpc\Decorators\CacheableRpcClient;
use ScayTrase\Api\Rpc\Decorators\LoggableRpcClient;
use ScayTrase\Api\Rpc\RpcRequestInterface;

class DecoratorTest extends AbstractRpcTest
{
    public function testLoggableClient()
    {
        if (!interface_exists(LoggerInterface::class)) {
            self::markTestSkipped('install psr/log in order to run these tests');
        }

        $logger = self::getMock(AbstractLogger::class, ['log']);
        $logger->expects(self::atLeastOnce())->method('log');

        $rq1 = $this->getRequestMock('/test1', ['param1' => 'test']);
        $rs1 = $this->getResponseMock(['param1' => 'test']);
        /** @var RpcRequestInterface[] $requests */
        $requests   = [$rq1];

        $client = new LoggableRpcClient($this->getClientMock([$rq1], [$rs1]), $logger);

        self::assertEquals($rs1, $client->invoke($requests)->getResponse($rq1));
    }

    public function testCacheableClient()
    {
        if (!interface_exists(CacheItemPoolInterface::class)) {
            self::markTestSkipped('install psr/cache in order to run these tests');
        }

        $rq1 = $this->getRequestMock('/test1', ['param1' => 'test']);
        $rq2 = $this->getRequestMock('/test1', ['param1' => 'test']);
        $rs1 = $this->getResponseMock(['payload' => bin2hex(random_bytes(20))]);

        $cache = $this->getCache();

        $client = new CacheableRpcClient($this->getClientMock([$rq1], [$rs1]), $cache, 5);
        $response = $client->invoke([$rq1])->getResponse($rq1);
        self::assertEquals($rs1, $response);

        $client = new CacheableRpcClient($this->getClientMock(), $cache, 5);
        $response = $client->invoke([$rq2])->getResponse($rq2);
        self::assertEquals($rs1, $response);
    }

    /**
     * @return CacheItemPoolInterface
     */
    private function getCache()
    {
        static $items = [];
        $cache = $this->prophesize(CacheItemPoolInterface::class);
        $that = $this;
        $cache->getItem(Argument::type('string'))->will(function ($args) use (&$items, $that) {
            $key = $args[0];
            if (!array_key_exists($key, $items)) {
                $item = $that->prophesize(CacheItemInterface::class);

                $item->getKey()->willReturn($key);
                $item->isHit()->willReturn(false);
                $item->set(Argument::any())->will(function ($args) use ($item) {
                    $item->get()->willReturn($args[0]);
                });
                $item->expiresAfter(Argument::type('int'))->willReturn($item);
                $item->expiresAfter(Argument::exact(null))->willReturn($item);
                $item->expiresAfter(Argument::type(\DateInterval::class))->willReturn($item);
                $item->expiresAt(Argument::type(\DateTimeInterface::class))->willReturn($item);
                $items[$key] = $item;
            }

            return $items[$key]->reveal();
        });
        $cache->save(Argument::type(CacheItemInterface::class))->will(function ($args) use (&$items) {
            $item = $args[0];
            $items[$item->getKey()]->isHit()->willReturn(true);
        });

        return $cache->reveal();
    }
}
