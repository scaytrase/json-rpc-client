<?php

namespace ScayTrase\Api\JsonRpc\Tests;

use GuzzleHttp\Handler\MockHandler as GuzzleMockHandler;

class MockHandler extends GuzzleMockHandler
{
    private $append = false;

    /**
     * Prevent empty queue being countable (PHP 7.2)
     *
     * @see https://github.com/guzzle/guzzle/pull/1809
     */
    public function append()
    {
        $this->append = true;

        return call_user_func_array(['parent', 'append'], func_get_args());
    }

    public function count()
    {
        if (!$this->append) {
            return 0;
        }

        return parent::count();
    }
}
