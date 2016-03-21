<?php
/**
 * Created by PhpStorm.
 * User: batanov.pavel
 * Date: 18.03.2016
 * Time: 15:16
 */

namespace ScayTrase\Api\Rpc\Decorators;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use ScayTrase\Api\Rpc\RpcClientInterface;

final class LoggableRpcClient implements RpcClientInterface
{
    /** @var  LoggerInterface */
    private $logger;
    /** @var  RpcClientInterface */
    private $decoratedClient;

    /**
     * LoggableRpcClient constructor.
     *
     * @param RpcClientInterface $decoratedClient
     * @param LoggerInterface    $logger
     */
    public function __construct(RpcClientInterface $decoratedClient, LoggerInterface $logger = null)
    {
        $this->decoratedClient = $decoratedClient;
        $this->logger          = $logger;

        if (null === $this->logger) {
            $this->logger = new NullLogger();
        }
    }

    /** {@inheritdoc} */
    public function invoke($calls)
    {
        if (!is_array($calls)) {
            $calls = [$calls];
        }

        foreach ($calls as $call) {
            $this->logger->debug(
                sprintf('%s Invoking RPC method "%s"', spl_object_hash($call), $call->getMethod()),
                json_decode(json_encode($call->getParameters()), true)
            );
        }

        return new LoggableResponseCollection($this->decoratedClient->invoke($calls), $this->logger);
    }
}
