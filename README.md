[![Build Status](https://travis-ci.org/scaytrase/json-rpc-client.svg?branch=master)](https://travis-ci.org/scaytrase/json-rpc-client)
[![Code Coverage](https://scrutinizer-ci.com/g/scaytrase/json-rpc-client/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/scaytrase/json-rpc-client/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/scaytrase/json-rpc-client/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/scaytrase/json-rpc-client/?branch=master)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/9706918a-39d4-4822-8e25-d0a01182b10b/mini.png)](https://insight.sensiolabs.com/projects/9706918a-39d4-4822-8e25-d0a01182b10b)


[![Latest Stable Version](https://poser.pugx.org/scaytrase/json-rpc-client/v/stable)](https://packagist.org/packages/scaytrase/json-rpc-client)
[![Total Downloads](https://poser.pugx.org/scaytrase/json-rpc-client/downloads)](https://packagist.org/packages/scaytrase/json-rpc-client)
[![Latest Unstable Version](https://poser.pugx.org/scaytrase/json-rpc-client/v/unstable)](https://packagist.org/packages/scaytrase/json-rpc-client)

# JSON-RPC 2.0 Client implementation

Extension of [`scaytrase/rpc-common`](https://github.com/scaytrase/rpc-common) 

 * JSON RPC Interfaces
 * JSON RPC client
 * Async with Guzzle
 * Automatic batch with multiple requests or `LazyClientDecorator`

[JSON-RPC 2.0 Specification](http://www.jsonrpc.org/specification)

## Usage

1. Configure a [Guzzle client](http://docs.guzzlephp.org/en/latest/).
2. Configure a Guzzle URI instance
3. Instantiate the client:

```php
use ScayTrase\Api\JsonRpc\JsonRpcClient;

$client = new JsonRpcClient($guzzleClient, new Uri('http://endpoint/url/'));
```

4. Optionally pass the ID generator as third argument and the PSR-3 logger as the fourth argument

Simple UUID generator and PSR-3 `NullLogger` are used by default. ID is generated for `RpcRequestInterface` instances.
If request is instance of `JsonRpcRequestInterface` and does not contain an ID assigned, the request is traited as 
notification request and will not receive the response from server.

5. Create a `RpcRequestInterface` instance

With `JsonRpcRequest` class:

```php
$request = new \ScayTrase\Api\JsonRpc\JsonRpcRequest('my/method', ['param1' => 'val1'], 'request_id');
$notification = new \ScayTrase\Api\JsonRpc\JsonRpcRequest('my/method', ['param1' => 'val1']);
```

With `JsonRpcNotification` class:
```php
$notification = new \ScayTrase\Api\JsonRpc\JsonRpcNotification('my/method', ['param1' => 'val1']);
```

With custom `RpcRequestInterface` implementation:

```php
final class MyRpcRequest implements \ScayTrase\Api\Rpc\RpcRequestInterface 
{
    public function getMethod() 
    {
        return 'my/method';
    }
    
    public function getParameters() 
    {
        return ['param1' => 'val1'];      
    }
}

$request = new MyRpcRequest;
```

6. Call the client

```php
/** @var \ScayTrase\Api\Rpc\RpcClientInterface $client */
/** @var \ScayTrase\Api\Rpc\RpcRequestInterface $request */
 
$collection = $client->invoke($request);
$collection = $client->invoke([$request]);
```

The collection object contains the mapping between the requests and the responses

```php
/** @var \ScayTrase\Api\Rpc\RpcRequestInterface $request */
/** @var \ScayTrase\Api\Rpc\ResponseCollectionInterface $collection */

$response = $collection->getResponse($request);
```

## Decorating

Refer [`scaytrase/rpc-common`](https://github.com/scaytrase/rpc-common) base library for some
useful decorators, i.e `CacheableRpcClient`, `LazyRpcClient`, `LoggableRpcClient`.

Also some profiling wrappers for Symfony usage were implemented:

 * [Profiled client](https://github.com/bankiru/doctrine-api-bundle/blob/master/src/Bankiru/Api/Client/Profiling/ProfiledClient.php)
 with DataCollector and Profiler integration
 * [Traceable client](https://github.com/bankiru/doctrine-api-bundle/blob/master/src/Bankiru/Api/Client/Profiling/TraceableClient.php)
 with Stopwatch integration
