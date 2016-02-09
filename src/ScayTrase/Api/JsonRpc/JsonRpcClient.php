<?php
/**
 * User: scaytrase
 * Date: 2016-01-02
 * Time: 21:41
 */

namespace ScayTrase\Api\JsonRpc;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\UriInterface;
use ScayTrase\Api\IdGenerator\IdGeneratorInterface;
use ScayTrase\Api\IdGenerator\UuidGenerator;
use ScayTrase\Api\Rpc\Exception\RemoteCallFailedException;
use ScayTrase\Api\Rpc\RpcClientInterface;
use ScayTrase\Api\Rpc\RpcRequestInterface;

final class JsonRpcClient implements RpcClientInterface
{
    const VERSION = '2.0';

    /**
     * @var ClientInterface
     */
    private $client;
    /** @var UriInterface */
    private $uri;
    /** @var IdGeneratorInterface */
    private $idGenerator;

    /**
     * JsonRpcClient constructor.
     * @param ClientInterface $client
     * @param UriInterface $endpoint
     * @param IdGeneratorInterface|null $idGenerator
     */
    public function __construct(ClientInterface $client, UriInterface $endpoint, IdGeneratorInterface $idGenerator = null)
    {
        $this->client = $client;
        $this->uri = $endpoint;
        $this->idGenerator = $idGenerator;

        if (null === $this->idGenerator) {
            $this->idGenerator = new UuidGenerator();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function invoke($calls)
    {
        if (!is_array($calls) && ($calls instanceof RpcRequestInterface)) {
            $calls = (array)$calls;
        }

        $requests = [];
        $requestBody = [];

        foreach ($calls as $key => $call) {
            $transformedCall = $this->transformCall($call);
            $requests[spl_object_hash($call)] = new RequestTransformation($call, $transformedCall);
            $requestBody[] = $this->formatJsonRpcCall($transformedCall);
        }

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $request = new Request(
            'POST',
            $this->uri,
            [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ],
            json_encode($requestBody, JSON_PRETTY_PRINT)
        );

        try {
            return new JsonRpcResponseCollection($this->client->sendAsync($request), $requests);
        } catch (GuzzleException $exception) {
            throw new RemoteCallFailedException($exception->getMessage(), 0, $exception);
        }
    }

    /**
     * @param RpcRequestInterface $call
     * @return JsonRpcRequest
     */
    private function transformCall(RpcRequestInterface $call)
    {
        $transformedCall = $call;
        if ($call instanceof RpcRequestInterface && !($call instanceof JsonRpcRequestInterface)) {
            $transformedCall = JsonRpcRequest::fromRpcRequest($call, $this->idGenerator->getRequestIdentifier($call));
            return $transformedCall;
        }
        return $transformedCall;
    }

    /**
     * @param JsonRpcRequestInterface|RpcRequestInterface $request
     * @return array
     */
    private function formatJsonRpcCall(JsonRpcRequestInterface $request)
    {
        $result = [
            'jsonrpc' => static::VERSION,
            'method' => $request->getMethod(),
            'params' => $request->getParameters(),
        ];

        if (!$request->isNotification()) {
            $result['id'] = $request->getId();
        }

        return $result;
    }
}
