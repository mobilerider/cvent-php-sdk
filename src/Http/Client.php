<?php

namespace Mr\CventSdk\Http;

use Mr\Bootstrap\Data\JsonEncoder;
use Mr\Bootstrap\Interfaces\HttpDataClientInterface;
use Mr\Bootstrap\Traits\HttpDataClient;

class Client extends \GuzzleHttp\Client implements HttpDataClientInterface
{
    use HttpDataClient;

    public function __construct(array $config = [])
    {
        parent::__construct($config);

        $this->setDataEncoder(new JsonEncoder());
    }
}
