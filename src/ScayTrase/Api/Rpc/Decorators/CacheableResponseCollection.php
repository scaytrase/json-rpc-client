<?php
/**
 * Created by PhpStorm.
 * User: batanov.pavel
 * Date: 18.03.2016
 * Time: 15:27
 */

namespace ScayTrase\Api\Rpc\Decorators;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use ScayTrase\Api\Rpc\ResponseCollectionInterface;
use ScayTrase\Api\Rpc\RpcRequestInterface;

final class CacheableResponseCollection implements \IteratorAggregate, ResponseCollectionInterface
{
    /** @var  CacheItemPoolInterface */
    private $cache;
    /** @var  RequestKeyExtractor */
    private $extractor;
    /** @var  array */
    private $items;
    /** @var  ResponseCollectionInterface */
    private $proxiedCollection;
    /** @var  int|null */
    private $ttl;

    /**
     * CacheableResponseCollection constructor.
     *
     * @param CacheItemPoolInterface      $cache
     * @param RequestKeyExtractor         $extractor
     * @param array                       $items
     * @param ResponseCollectionInterface $proxiedCollection
     */
    public function __construct(
        CacheItemPoolInterface $cache,
        RequestKeyExtractor $extractor,
        array $items,
        ResponseCollectionInterface $proxiedCollection,
        $ttl
    )
    {
        $this->cache             = $cache;
        $this->extractor         = $extractor;
        $this->items             = $items;
        $this->proxiedCollection = $proxiedCollection;
        $this->ttl               = $ttl ?: null;
    }


    /** {@inheritdoc} */
    public function getResponse(RpcRequestInterface $request)
    {
        $key = $this->extractor->getKey($request);

        /** @var CacheItemInterface $item */
        $item = $this->items[$key]['item'];

        if ($item->isHit()) {
            return $item->get();
        }

        $item->expiresAfter($this->ttl);
        $item->set($this->proxiedCollection->getResponse($request));

        $this->cache->save($item);

        return $item->get();
    }

    /** {@inheritdoc} */
    public function getIterator()
    {
        foreach ($this->items as $key => $data) {
            /** @var CacheItemInterface $item */
            $item = $data['item'];

            if ($item->isHit()) {
                yield $item->get();
            }

            yield $this->getResponse($data['request']);
        }
    }
}
