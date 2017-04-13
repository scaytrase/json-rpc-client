<?php

namespace ScayTrase\Api\JsonRpc;

use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\ResponseInterface;
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
    protected $hashedResponses = [];
    /** @var  RequestTransformation[] */
    private $transformations;
    /** @var  PromiseInterface */
    private $promise;
    /** @var  JsonRpcResponseInterface[] */
    private $responses = [];

    /** @var bool */
    private $synchronized = false;

    /**
     * JsonRpcResponseCollection constructor.
     * @param PromiseInterface $promise
     * @param RequestTransformation[] $transformations
     */
    public function __construct(PromiseInterface $promise, array $transformations)
    {
        $this->promise = $promise;
        $this->transformations = $transformations;
    }

    /** {@inheritdoc} */
    public function getIterator()
    {
        $this->sync();
        return new \ArrayIterator($this->responses);
    }

    private function sync()
    {
        if ($this->synchronized) {
            return;
        }

        /** @var ResponseInterface $clientResponse */
        $clientResponse = $this->promise->wait();
        if (200 !== $clientResponse->getStatusCode()) {
            throw new RemoteCallFailedException();
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
                //todo: logging??? (@scaytrase)
                continue;
            }

            $this->responses[$response->getId()] = $response;
        }

        $this->synchronized = true;
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
}
