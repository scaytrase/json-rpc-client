<?php

namespace ScayTrase\Api\JsonRpc;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use ScayTrase\Api\JsonRpc\Exception\JsonRpcProtocolException;
use ScayTrase\Api\JsonRpc\Exception\ResponseParseException;
use ScayTrase\Api\Rpc\Exception\RemoteCallFailedException;
use ScayTrase\Api\Rpc\ResponseCollectionInterface;
use ScayTrase\Api\Rpc\RpcRequestInterface;

/**
 * Class JsonRpcResponseCollection
 *
 * Json Rpc Collection contains responses only for non-notification responses
 * getResponse method will provide response stub for notification if request was successful
 */
final class JsonRpcResponseCollection implements \IteratorAggregate, ResponseCollectionInterface
{
    /** @var JsonRpcResponseInterface[] */
    private $hashedResponses = [];
    /** @var  RequestTransformation[] */
    private $transformations;
    /** @var  PromiseInterface */
    private $promise;
    /** @var  JsonRpcResponseInterface[] */
    private $responses = [];

    /** @var bool */
    private $synchronized = false;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * JsonRpcResponseCollection constructor.
     * @param PromiseInterface $promise
     * @param RequestTransformation[] $transformations
     * @param LoggerInterface|null $logger
     */
    public function __construct(PromiseInterface $promise, array $transformations, LoggerInterface $logger = null)
    {
        $this->promise = $promise;
        $this->transformations = $transformations;
        $this->logger = $logger ?: new NullLogger();
    }

    /** {@inheritdoc} */
    public function getIterator()
    {
        $this->sync();

        return new \ArrayIterator($this->responses);
    }

    /** {@inheritdoc} */
    public function getResponse(RpcRequestInterface $request)
    {
        if (array_key_exists(spl_object_hash($request), $this->hashedResponses)) {
            return $this->hashedResponses[spl_object_hash($request)];
        }

        $this->sync();

        $storedRequest = null;
        foreach ($this->transformations as $transformation) {
            if ($transformation->getOriginalCall() === $request) {
                $storedRequest = $transformation->getTransformedCall();
                break;
            }
        }

        if (null === $storedRequest) {
            throw new \OutOfBoundsException('Given request was not invoked for this collection');
        }

        if ($storedRequest->isNotification()) {
            $this->hashedResponses[spl_object_hash($request)] = new JsonRpcNotificationResponse();

            return $this->hashedResponses[spl_object_hash($request)];
        }

        if (!array_key_exists($storedRequest->getId(), $this->responses)) {
            throw JsonRpcProtocolException::requestSendButNotResponded($storedRequest);
        }

        $this->hashedResponses[spl_object_hash($request)] = $this->responses[$storedRequest->getId()];

        return $this->hashedResponses[spl_object_hash($request)];
    }

    private function sync()
    {
        if ($this->synchronized) {
            return;
        }

        try {
            /** @var ResponseInterface $clientResponse */
            $clientResponse = $this->promise->wait();
            if (200 !== $clientResponse->getStatusCode()) {
                throw new RemoteCallFailedException('Response was not successful');
            }
        } catch (GuzzleException $exception) {
            throw new RemoteCallFailedException($exception->getMessage(), 0, $exception);
        }

        $data = (string)$clientResponse->getBody();

        // Null (empty response) is expected if only notifications were sent
        $rawResponses = [];

        if ('' !== $data) {
            $rawResponses = json_decode($data, false);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw ResponseParseException::notAJsonResponse();
            }
        }

        if (!is_array($rawResponses) && $rawResponses instanceof \stdClass) {
            $rawResponses = [$rawResponses];
        }

        $this->responses = [];
        foreach ($rawResponses as $rawResponse) {
            try {
                $response = new SyncResponse($rawResponse);
            } catch (ResponseParseException $exception) {
                $this->logger->warning(
                    'Invalid JSON-RPC response skipped: '.$exception->getMessage(),
                    [
                        'data' => json_decode(json_encode($rawResponse), true),
                    ]
                );

                continue;
            }

            $this->responses[$response->getId()] = $response;
        }

        $this->synchronized = true;
    }
}
