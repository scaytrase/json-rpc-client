<?php
/**
 * Created by PhpStorm.
 * User: batanov.pavel
 * Date: 18.03.2016
 * Time: 15:31
 */

namespace ScayTrase\Api\Rpc\Decorators;

use ScayTrase\Api\Rpc\RpcRequestInterface;

/** @internal */
class RequestKeyExtractor
{
    /** @var  string */
    private $keyPrefix;

    /**
     * RequestKeyExtractor constructor.
     *
     * @param $keyPrefix
     */
    public function __construct($keyPrefix) { $this->keyPrefix = $keyPrefix; }


    public function getKey(RpcRequestInterface $request)
    {
        $data = [
            'method' => (string)$request->getMethod(),
            'params' => json_decode(json_encode($request->getParameters()), true),
        ];

        $stringData = json_encode($data);

        return $this->keyPrefix . sha1($stringData);
    }
}
