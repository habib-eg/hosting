<?php
namespace Habib\Hosting\Base;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/**
 * Class Hosting
 * @package Habib\Hosting\Base
 */
abstract class Hosting
{

    /**
     * @var string Username of your whm server. Must be string
     *
     * @since v1.0.0
     */
    protected $username;

    /**
     * @var string Password or long hash of your whm server.
     *
     * @since v1.0.0
     */
    protected $password;

    /**
     * @var string Authentication type you want to use. You can set as 'hash' or 'password'.
     *
     * @since v1.0.0
     */
    protected $auth_type;

    /**
     * @var string Host of your whm server. You must set it with full host with its port and protocol.
     *
     * @since v1.0.0
     */
    protected $hostName;

    /**
     * @var array
     */
    protected $headers = [];

    /**
     * @var PendingRequest
     */
    protected $http;

    /**
     * https://docs.guzzlephp.org/en/stable/request-options.html
     * @var array
     */
    protected $options = [];

    /**
     * Hosting constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->setHostName("{$config['protocol']}://{$config['host']}:{$config['port']}/" );
        $this->setUsername($config['username']);
        $this->setPassword($config['password']);
        $this->setAuthType($config['auth_type']);
        $this->setHeaders($config['headers'] ?? []);
        $this->setOptions($config['options'] ?? []);
        $this->setHttp();
    }

    /**
     * @param PendingRequest $http
     * @return self
     */
    public function setHttp($http=null): self
    {
        $this->http = $http ?? Http::withOptions($this->options)->withHeaders($this->headers);
        return  $this;
    }

    /**
     * @param string $auth_type
     * @return $this
     */
    public function setAuthType(string $auth_type): self
    {
        $this->auth_type = $auth_type;
        return  $this;
    }

    /**
     * @return string
     */
    public function getAuthType(): string
    {
        return $this->auth_type;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername(string $username): self
    {
        $this->username = $username;
        return  $this;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword(string $password): self
    {
        $this->password = $password;
        return  $this;
    }

    /**
     * @return string
     */
    public function getHostName(): string
    {
        return $this->hostName;
    }

    /**
     * @param string $hostName
     */
    public function setHostName(string $hostName): self
    {
        $this->hostName = $hostName;
        return  $this;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param array $options
     * @return $this
     */
    public function setOptions(array $options): self
    {
        $this->options = $options;
        return  $this;
    }

    /**
     * @param array $options
     * @return $this
     */
    public function addOptions(array $options): self
    {
        $this->options = array_merge($this->options,$options);
        return  $this;
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @param array $headers
     * @return $this
     */
    public function setHeaders(array $headers): self
    {
        $this->headers = $headers;
        return  $this;
    }
    /**
     * @param array $headers
     * @return $this
     */
    public function addHeader(array $headers): self
    {
        $this->headers =array_merge($this->headers,$headers);
        return  $this;
    }

    /**
     * @return PendingRequest
     */
    public function getHttp(): PendingRequest
    {
        return $this->http;
    }

    /**
     * checking options for 'username', 'password', and 'host'. If they are not set, some exception will be thrown.
     *
     * @param array $options list of options that will be checked
     *
     * @return self
     * @throws \Exception
     * @since v1.0.0
     */
    private function checkOptions()
    {
        if (empty($this->username) || !($this->username)) {
            throw new \Exception('Username is not set', 422);
        }
        if (empty($this->password) || !($this->password)) {
            throw new \Exception('Password or hash is not set', 422);
        }
        if (empty($this->hostName) || !($this->hostName)) {
            throw new \Exception('CPanel Host is not set', 422);
        }

        return $this;
    }

    /**
     * set authorization for access.
     * It only set 'username' and 'password'.
     *
     * @param string $username Username of your whm server.
     * @param string $password Password or long hash of your whm server.
     *
     * @return object return as self-object
     *
     * @since v1.0.0
     */
    public function setAuthorization($username, $password)
    {
        $this->username = $username;
        $this->password = $password;

        return $this;
    }


    /**
     * @return $this
     */
    abstract protected function addAuthorization();

    /**
     * @param $action
     * @param array $arguments
     * @param false $throw
     * @return mixed
     */
    abstract protected function execute($action, $arguments = [], $throw = false);

}
