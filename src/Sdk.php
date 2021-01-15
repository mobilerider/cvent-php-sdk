<?php

namespace Mr\CventSdk;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use Mr\CventSdk\Exception\CventException;
use Mr\CventSdk\Exception\InvalidCredentialsException;
use Mr\CventSdk\Http\Client;
use Mr\CventSdk\Http\Middleware\ErrorsMiddleware;
use Mr\CventSdk\Http\Middleware\TokenAuthMiddleware;
use Mr\CventSdk\Repository\Registration\AttendeeRepository;
use Mr\CventSdk\Repository\Registration\EventRepository;
use Mr\CventSdk\Service\RegistrationService;
use Mr\CventSdk\Model\Registration\Attendee;
use Mr\CventSdk\Model\Registration\Event;
use Mr\Bootstrap\Container;
use Mr\Bootstrap\Interfaces\ContainerAccessorInterface;
use Mr\Bootstrap\Traits\ContainerAccessor;
use Mr\Bootstrap\Utils\Logger;

/**
 * @method static RegistrationService getRegistrationService
 * @method static string getAccountId
 *
 * Class Sdk
 * @package Mr\CventSdk
 */
class Sdk implements ContainerAccessorInterface
{
    use ContainerAccessor;

    const BASE_URL = 'https://api-platform.Cvent.com/';

    const API_VERSION = 'ea/';

    private static $instance;
    private $clientId;
    private $clientSecret;
    private $token;
    private $options;
    private $httpOptions;

    private $defaultHeaders = [
        'Accept' => 'application/json',
        'Content-Type' => 'application/x-www-form-urlencoded'
    ];

    /**
     * Service constructor.
     * @param $accountId
     * @param $appId
     * @param $appSecret
     * @param string $token
     * @param array $options
     * @param array $httpOptions
     * @throws MrException
     */
    private function __construct($clientId, $clientSecret, $token = null, array $options = [], array $httpOptions = [])
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->token = $token;
        $this->options = $options;

        $httpCommon = [
            "debug" => $this->options["debug"] ?? false
        ];

        $this->httpOptions = [
            'registration' => array_merge(
                [
                    'base_uri' => static::BASE_URL,
                    'headers' => $this->defaultHeaders
                ],
                $httpCommon,
                $httpOptions['registration'] ?? []
            ),
        ];

        if ((!$clientSecret || !$clientId) && !$token) {
            throw new CventException('Empty credentials');
        }

        if (!$token) {
            $token = $this->authenticate();
        }

        // Create default handler with all the default middlewares
        $stack = HandlerStack::create();
        $stack->remove('http_errors');
        $stack->unshift(new TokenAuthMiddleware($token), 'auth');

        // Last to un-shift so it remains first to execute
        $stack->unshift(new ErrorsMiddleware([]), 'http_errors');
        $httpDefaultRuntimeOptions = [
            'handler' => $stack,
        ];

        $customDefinitions = isset($options['definitions']) ? $options['definitions'] : [];

        $definitions = $customDefinitions + [
                'Logger' => [
                    'single' => true,
                    'instance' => Logger::getInstance(),
                ],
                // Clients
                'RegistrationClient' => [
                    'single' => true,
                    'class' => Client::class,
                    'arguments' => [
                        'options' => array_merge($httpDefaultRuntimeOptions, $this->httpOptions['registration'])
                    ]
                ],
                // Services
                RegistrationService::class => [
                    'single' => true,
                    'class' => RegistrationService::class,
                    'arguments' => [
                        'client' => \mr_srv_arg('RegistrationClient')
                    ]
                ],
                // Repositories
                AttendeeRepository::class => [
                    'single' => true,
                    'class' => AttendeeRepository::class,
                    'arguments' => [
                        'client' => \mr_srv_arg('RegistrationClient'),
                        'options' => []
                    ]
                ],
                EventRepository::class => [
                    'single' => true,
                    'class' => EventRepository::class,
                    'arguments' => [
                        'client' => \mr_srv_arg('RegistrationClient'),
                        'options' => []
                    ]
                ],
                // Models
                Attendee::class => [
                    'single' => false,
                    'class' => Attendee::class,
                    'arguments' => [
                        'repository' => \mr_srv_arg(AttendeeRepository::class),
                        'data' => null
                    ]
                ],
                Event::class => [
                    'single' => false,
                    'class' => Event::class,
                    'arguments' => [
                        'repository' => \mr_srv_arg(EventRepository::class),
                        'data' => null
                    ]
                ],
            ];

        $this->container = new Container($definitions);
    }

    protected function isDebug()
    {
        return $this->options['debug'] ?? false;
    }

    protected function authenticate()
    {
        $encode = base64_encode($this->clientId.':'.$this->clientSecret);
       
        $headers = [
            'headers'=>[
              'Authorization' => 'Basic '.$encode,
              'Content-Type' => 'application/x-www-form-urlencoded'
            ]
        ];
        $client = new Client($headers);
        $data = null;
       
        try {
            $data = $client->request('POST', static::BASE_URL . static::API_VERSION . 'oauth2/token', ['form_params'=> [
               'grant_type' => 'client_credentials',
               'client_ider' => $this->clientId
            ]]);
        } catch (RequestException $ex) {
            // Just avoid request exception from propagating
            if ($this->isDebug()) {
                \mr_logger()->error($ex->getMessage());
            }
        }
        $body = $data->getBody();
       
        $data = json_decode($body, true);

        if (! isset($data, $data['access_token'])) {
            throw new InvalidCredentialsException();
        }
      
        return $this->token = $data['access_token'];
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    public function getHttpOptions()
    {
        return $this->httpOptions;
    }

    protected static function create($clientId, $clientSecret, $token, array $options, array $httpOptions)
    {
        self::$instance = new self($clientId, $clientSecret, $token, $options, $httpOptions);
    }

    public static function setCredentials($clientId, $clientSecret, array $options = [], array $httpOptions = [])
    {
        self::create($clientId, $clientSecret, null, $options, $httpOptions);
    }

    public static function setAuthToken($token, array $options = [], array $httpOptions = [])
    {
        self::create(null, null, $token, $options, $httpOptions);
    }

    /**
     * @return Sdk
     */
    protected static function getInstance()
    {
        if (!self::$instance) {
            throw new \RuntimeException('You need to set credentials or auth token first');
        }

        return self::$instance;
    }

    public static function isAuthenticated()
    {
        return (bool) self::$instance;
    }

    public static function __callStatic($name, $arguments)
    {
        $instance = self::getInstance();

        $name = '_' . $name;

        return call_user_func_array([$instance, $name], $arguments);
    }

    protected function _getClientId()
    {
        return $this->clientId;
    }

    protected function _getClientSecret()
    {
        return $this->clientSecret;
    }

    /**
     * @return token
     */
    protected function _getToken()
    {
        return $this->token;
    }


    /**
     * @return RegistrationService
     */
    protected function _getRegistrationService()
    {
        return $this->_get(RegistrationService::class);
    }
}
