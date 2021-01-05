<?php

namespace Mr\CventSdk\Http\Middleware;

use GuzzleHttp\Promise\Promise;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class TokenAuthMiddleware
{
    const AUTH_HEADER = 'Authorization';

    protected $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * @param callable $handler
     *
     * @return callable
     */
    public function __invoke(callable $handler)
    {
        return function (RequestInterface $request, array $options) use ($handler) {
            $request = $request->withAddedHeader('Authorization', 'Bearer '. $this->token);
       

            /** @var Promise $promise */
            return $handler($request, $options);
        };
    }
}
