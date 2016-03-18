<?php
/**
 * Created by PhpStorm.
 * User: batanov.pavel
 * Date: 18.03.2016
 * Time: 15:26
 */

namespace ScayTrase\Api\Rpc\Decorators;

use Psr\Cache\CacheItemPoolInterface;
use ScayTrase\Api\Rpc\RpcClientInterface;

final class CacheableRpcClient implements RpcClientInterface
{
    const DEFAULT_KEY_PREFIX = 'rpc_client_cache';

    /** @var  CacheItemPoolInterface */
    private $cache;
    /** @var  RpcClientInterface */
    private $decoratedClient;
    /** @var  RequestKeyExtractor */
    private $extractor;
    /** @var int|null */
    private $ttl;

    /**
     * CacheableRpcClient constructor.
     *
     * @param RpcClientInterface     $decoratedClient
     * @param CacheItemPoolInterface $cache
     * @param int|null               $ttl
     * @param string                 $keyPrefix
     */
    public function __construct(
        RpcClientInterface $decoratedClient,
        CacheItemPoolInterface $cache,
        $ttl = null,
        $keyPrefix = self::DEFAULT_KEY_PREFIX)
    {
        $this->decoratedClient = $decoratedClient;
        $this->cache           = $cache;
        $this->ttl             = $ttl;

        $this->extractor       = new RequestKeyExtractor((string)$keyPrefix);
    }

    /** {@inheritdoc} */
    public function invoke($calls)
    {
        if (!is_array($calls)) {
            $calls = [$calls];
        }

        $items           = [];
        $proxiedRequests = [];
        foreach ($calls as $call) {
            $key                    = $this->extractor->getKey($call);
            $item                   = $this->cache->getItem($key);
            $items[$key]['request'] = $call;
            $items[$key]['item']    = $item;
            if (!$item->isHit()) {
                $proxiedRequests[] = $call;
            }
        }

        // Prevent batch calls when not necessary
        if (count($proxiedRequests) === 1 && !is_array($calls)) {
            $proxiedRequests = array_shift($proxiedRequests);
        }

        return new CacheableResponseCollection(
            $this->cache,
            $this->extractor,
            $items,
            $this->decoratedClient->invoke($proxiedRequests),
            $this->ttl
        );
    }
}
