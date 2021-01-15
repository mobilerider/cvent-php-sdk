<?php

namespace Mr\CventSdk\Http\Middleware;

use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ErrorsMiddleware
{
    protected $errorHandlers;

    public function __construct(array $errorHandlers)
    {
        $this->errorHandlers = $errorHandlers;
    }

    /**
     * @param callable $handler
     *
     * @return callable
     */
    public function __invoke(callable $handler)
    {
        return function (RequestInterface $request, array $options) use ($handler) {
            return $handler($request, $options)->then(
                function (ResponseInterface $response) use ($request, $handler) {
                    $code = $response->getStatusCode();

                    if ($code < 400) {
                        return $response;
                    }

                    if (isset($this->errorHandlers[$code])) {
                        return $this->errorHandlers[$code]();
                    }

                    throw RequestException::create($request, $response);
                }
            );
        };
    }
}