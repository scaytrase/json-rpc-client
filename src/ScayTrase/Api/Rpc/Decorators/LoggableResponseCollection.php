<?php
/**
 * Created by PhpStorm.
 * User: batanov.pavel
 * Date: 18.03.2016
 * Time: 15:20
 */

namespace ScayTrase\Api\Rpc\Decorators;

use Psr\Log\LoggerInterface;
use ScayTrase\Api\Rpc\ResponseCollectionInterface;
use ScayTrase\Api\Rpc\RpcRequestInterface;

final class LoggableResponseCollection implements \IteratorAggregate, ResponseCollectionInterface
{
    /** @var  LoggerInterface */
    private $logger;
    /** @var  ResponseCollectionInterface */
    private $decoratedCollection;

    /**
     * LoggableResponseCollection constructor.
     *
     * @param ResponseCollectionInterface $decoratedCollection
     * @param LoggerInterface             $logger
     */
    public function __construct(ResponseCollectionInterface $decoratedCollection, LoggerInterface $logger)
    {
        $this->decoratedCollection = $decoratedCollection;
        $this->logger              = $logger;
    }

    /** {@inheritdoc} */
    public function getResponse(RpcRequestInterface $request)
    {
        $response = $this->decoratedCollection->getResponse($request);

        $this->logger->debug(
            sprintf(
                '%s Response for RPC method "%s" is %s',
                spl_object_hash($request),
                $request->getMethod(),
                $response->isSuccessful() ? 'Successful' : 'Failed'
            ),
            json_decode(json_encode($response->getBody()), true)
        );
        if ($response->isSuccessful()) {
            $this->logger->debug(
                sprintf('%s Response for RPC method "%s"', spl_object_hash($request), $request->getMethod()),
                json_decode(json_encode($response->getBody()), true)
            );
        } else {
            $this->logger->debug(
                sprintf('%s Response for RPC method "%s"', spl_object_hash($request), $request->getMethod()),
                json_decode(json_encode($response->getError()), true)
            );
        }

        return $response;
    }

    /** {@inheritdoc} */
    public function count()
    {
        return $this->decoratedCollection->count();
    }

    /** {@inheritdoc} */
    public function getIterator()
    {
        foreach ($this->decoratedCollection as $response) {
            //todo: log
            yield $response;
        }
    }
}
