<?php
/**
 * User: scaytrase
 * Date: 2016-01-02
 * Time: 22:00
 */

namespace ScayTrase\Api\IdGenerator;

use ScayTrase\Api\Rpc\RpcRequestInterface;

/**
 * Class UuidGenerator
 * @link http://stackoverflow.com/a/15875555/1361089
 */
final class UuidGenerator implements IdGeneratorInterface
{
    /**
     * @param RpcRequestInterface|null $request
     * @return string
     */
    public function getRequestIdentifier(RpcRequestInterface $request = null)
    {
        /** @var string $data */
        $data = random_bytes(16);

        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
