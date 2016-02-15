<?php
/**
 * Created by PhpStorm.
 * User: batanov.pavel
 * Date: 08.02.2016
 * Time: 10:34
 */

namespace ScayTrase\Api\Rpc;

final class LazyResponseCollection implements \IteratorAggregate, ResponseCollectionInterface
{
    /** @var bool */
    private $initialized = false;
    /** @var RpcRequestInterface[] */
    private $requests = [];
    /** @var  RpcClientInterface */
    private $client;
    /** @var  ResponseCollectionInterface */
    private $collection;

    /**
     * LazyResponseCollection constructor.
     *
     * @param RpcClientInterface $client
     */
    public function __construct(RpcClientInterface $client) { $this->client = $client; }


    /** {@inheritdoc} */
    public function getResponse(RpcRequestInterface $request)
    {
        if (!$this->initialized) {
            $this->init();
        }

        return $this->collection->getResponse($request);
    }

    private function init()
    {
        $this->collection  = $this->client->invoke($this->requests);
        $this->requests    = null;
        $this->initialized = true;
    }

    /** {@inheritdoc} */
    public function count()
    {
        if (!$this->initialized) {
            $this->init();
        }

        return $this->collection->count();
    }

    public function append(RpcRequestInterface $request)
    {
        if ($this->isFrozen()) {
            throw new \LogicException('Cannot add request to frozen lazy collection');
        }

        $this->requests[] = $request;
    }

    public function isFrozen()
    {
        return $this->initialized;
    }

    /** {@inheritdoc} */
    public function getIterator()
    {
        if (!$this->initialized) {
            $this->init();
        }

        return $this->collection;
    }
}
