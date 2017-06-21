<?php

namespace ScayTrase\Api\JsonRpc;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\UriInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use ScayTrase\Api\IdGenerator\IdGeneratorInterface;
use ScayTrase\Api\IdGenerator\UuidGenerator;
use ScayTrase\Api\Rpc\Exception\RemoteCallFailedException;
use ScayTrase\Api\Rpc\RpcClientInterface;
use ScayTrase\Api\Rpc\RpcRequestInterface;

final class JsonRpcClient implements RpcClientInterface
{
    const VERSION = '2.0';

    /** @var ClientInterface */
    private $client;
    /** @var UriInterface|string */
    private $uri;
    /** @var IdGeneratorInterface */
    private $idGenerator;
    /** @var LoggerInterface */
    private $logger;

    /**
     * JsonRpcClient constructor.
     *
     * @param ClientInterface           $client
     * @param UriInterface|string       $endpoint
     * @param IdGeneratorInterface|null $idGenerator
     * @param LoggerInterface           $logger
     */
    public function __construct(
        ClientInterface $client,
        $endpoint,
        IdGeneratorInterface $idGenerator = null,
        LoggerInterface $logger = null
    ) {
        $this->client      = $client;
        $this->uri         = $endpoint;
        $this->idGenerator = $idGenerator ?: new UuidGenerator();
        $this->logger      = $logger ?: new NullLogger();
    }

    /** {@inheritdoc} */
    public function invoke($calls)
    {
        try {
            if (!is_array($calls)) {
                if (!($calls instanceof RpcRequestInterface)) {
                    throw new \InvalidArgumentException(
                        'Request should be either array or single RpcRequestInterface instance'
                    );
                }
                $transformedCall = $this->transformCall($calls);

                return new JsonRpcResponseCollection(
                    $this->client->sendAsync(
                        $this->createHttpRequest($transformedCall)
                    ),
                    [new RequestTransformation($calls, $transformedCall)]
                );
            }

            $requests     = [];
            $batchRequest = [];

            foreach ($calls as $key => $call) {
                $transformedCall                  = $this->transformCall($call);
                $requests[spl_object_hash($call)] = new RequestTransformation($call, $transformedCall);
                $batchRequest[]                   = $transformedCall;
            }

            return new JsonRpcResponseCollection(
                $this->client->sendAsync($this->createHttpRequest($batchRequest)),
                $requests,
                $this->logger
            );
        } catch (GuzzleException $exception) {
            throw new RemoteCallFailedException($exception->getMessage(), 0, $exception);
        }
    }

    /**
     * @param $requestBody
     *
     * @return Request
     */
    private function createHttpRequest($requestBody)
    {
        return new Request(
            'POST',
            $this->uri,
            [
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ],
            json_encode($requestBody, JSON_PRETTY_PRINT)
        );
    }

    /**
     * @param RpcRequestInterface $call
     *
     * @return JsonRpcRequestInterface
     */
    private function transformCall(RpcRequestInterface $call)
    {
        if ($call instanceof JsonRpcRequestInterface) {
            return $call;
        }

        return JsonRpcRequest::fromRpcRequest($call, $this->idGenerator->getRequestIdentifier($call));
    }
}
