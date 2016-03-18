<?php
/**
 * Created by PhpStorm.
 * User: batanov.pavel
 * Date: 08.02.2016
 * Time: 10:31
 */

namespace ScayTrase\Api\Rpc\Decorators;

use ScayTrase\Api\Rpc\RpcClientInterface;

final class LazyRpcClient implements RpcClientInterface
{
    /** @var  LazyResponseCollection */
    private $collection;
    /** @var  RpcClientInterface */
    private $decoratedClient;

    /**
     * LazyRpcClient constructor.
     *
     * @param RpcClientInterface $decoratedClient
     */
    public function __construct(RpcClientInterface $decoratedClient)
    {
        $this->decoratedClient = $decoratedClient;
        $this->collection      = new LazyResponseCollection($decoratedClient);
    }

    public function invoke($calls)
    {
        // Reset collection if previous was already sent
        if ($this->collection->isFrozen()) {
            $this->collection = new LazyResponseCollection($this->decoratedClient);
        }

        if (!is_array($calls)) {
            $calls = [$calls];
        }

        foreach ($calls as $call) {
            $this->collection->append($call);
        }

        return $this->collection;
    }
}
