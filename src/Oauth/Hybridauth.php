<?php

namespace Cidaas\OauthConnect\Oauth;

use Cidaas\OauthConnect\Oauth\Exception\InvalidArgumentException;
use Cidaas\OauthConnect\Oauth\Exception\UnexpectedValueException;
use Cidaas\OauthConnect\Oauth\Storage\StorageInterface;
use Cidaas\OauthConnect\Oauth\Logger\LoggerInterface;
use Cidaas\OauthConnect\Oauth\Logger\Logger;
use Cidaas\OauthConnect\Oauth\HttpClient\HttpClientInterface;


class Oauth
{
    /**
    * Oauth config.
    *
    * @var array
    */
    protected $config;

    /**
    * Storage.
    *
    * @var StorageInterface
    */
    protected $storage;

    /**
    * HttpClient.
    *
    * @var HttpClientInterface
    */
    protected $httpClient;

    /**
    * Logger.
    *
    * @var LoggerInterface
    */
    protected $logger;

    /**
    * @param array|string        $config     Array with configuration or Path to PHP file that will return array
    * @param HttpClientInterface $httpClient
    * @param StorageInterface    $storage
    * @param LoggerInterface     $logger
    *
    * @throws InvalidArgumentException
    */
    public function __construct(
        $config,
        HttpClientInterface $httpClient = null,
        StorageInterface $storage = null,
        LoggerInterface $logger = null
    ) {
        if (is_string($config) && file_exists($config)) {
            $config = include $config;
        } elseif (! is_array($config)) {
            throw new InvalidArgumentException('Oauth config does not exist on the given path.');
        }

        $this->config = $config + [
            'debug_mode'   => Logger::NONE,
            'debug_file'   => '',
            'curl_options' => null,
            'providers'    => []
        ];
        $this->storage = $storage;
        $this->logger = $logger;
        $this->httpClient = $httpClient;
    }

    /**
    * Instantiate the given provider and authentication or authorization protocol.
    *
    * If not authenticated yet, the user will be redirected to the provider's site for
    * authentication/authorisation, otherwise it will simply return an instance of
    * provider's adapter.
    *
    * @param string $name adapter's name (case insensitive)
    *
    * @return \Cidaas\OauthConnect\Oauth\Adapter\AdapterInterface
    * @throws InvalidArgumentException
    * @throws UnexpectedValueException
    */
    public function authenticate($name)
    {
        $adapter = $this->getAdapter($name);

        $adapter->authenticate();

        return $adapter;
    }

    /**
    * Returns a new instance of a provider's adapter by name
    *
    * @param string $name adapter's name (case insensitive)
    *
    * @return \Cidaas\OauthConnect\Oauth\Adapter\AdapterInterface
    * @throws InvalidArgumentException
    * @throws UnexpectedValueException
    */
    public function getAdapter($name)
    {
        $config = $this->getProviderConfig($name);

        $adapter = isset($config['adapter']) ? $config['adapter'] : sprintf('Cidaas\\OauthConnect\\Oauth\\Provider\\%s', $name);

        if (!class_exists($adapter)) {
            $adapter = null;
            $fs = new \FilesystemIterator(__DIR__ . '/Provider/');
            /** @var \SplFileInfo $file */
            foreach ($fs as $file) {
                if (!$file->isDir()) {
                    $provider = strtok($file->getFilename(), '.');
                    if ($name === mb_strtolower($provider)) {
                        $adapter = sprintf('Cidaas\\OauthConnect\\Oauth\\Provider\\%s', $provider);
                        break;
                    }
                }
            }
            if ($adapter === null) {
                throw new InvalidArgumentException('Unknown Provider.');
            }
        }

        return new $adapter($config, $this->httpClient, $this->storage, $this->logger);
    }

    /**
    * Get provider config by name.
    *
    * @param string $name adapter's name (case insensitive)
    *
    * @throws UnexpectedValueException
    * @throws InvalidArgumentException
    *
    * @return array
    */
    public function getProviderConfig($name)
    {
        $name = strtolower($name);

        $providersConfig = array_change_key_case($this->config['providers'], CASE_LOWER);

        if (! isset($providersConfig[$name])) {
            throw new InvalidArgumentException('Unknown Provider.');
        }

        if (! $providersConfig[$name]['enabled']) {
            throw new UnexpectedValueException('Disabled Provider.');
        }

        $config = $providersConfig[$name];
        $config += [
            'debug_mode' => $this->config['debug_mode'],
            'debug_file' => $this->config['debug_file'],
        ];

        if (! isset($config['callback']) && isset($this->config['callback'])) {
            $config['callback'] = $this->config['callback'];
        }

        return $config;
    }

    /**
    * Returns a boolean of whether the user is connected with a provider
    *
    * @param string $name adapter's name (case insensitive)
    *
    * @return boolean
    * @throws InvalidArgumentException
    * @throws UnexpectedValueException
    */
    public function isConnectedWith($name)
    {
        return $this->getAdapter($name)->isConnected();
    }

    /**
    * Returns a list of enabled adapters names
    *
    * @return array
    */
    public function getProviders()
    {
        $providers = [];

        foreach ($this->config['providers'] as $name => $config) {
            if ($config['enabled']) {
                $providers[] = $name;
            }
        }

        return $providers;
    }

    /**
    * Returns a list of currently connected adapters names
    *
    * @return array
    * @throws InvalidArgumentException
    * @throws UnexpectedValueException
    */
    public function getConnectedProviders()
    {
        $providers = [];

        foreach ($this->getProviders() as $name) {
            if ($this->isConnectedWith($name)) {
                 $providers[] = $name;
            }
        }

        return $providers;
    }

    /**
    * Returns a list of new instances of currently connected adapters
    *
    * @return \Cidaas\OauthConnect\Oauth\Adapter\AdapterInterface[]
    * @throws InvalidArgumentException
    * @throws UnexpectedValueException
    */
    public function getConnectedAdapters()
    {
        $adapters = [];

        foreach ($this->getProviders() as $name) {
            $adapter = $this->getAdapter($name);

            if ($adapter->isConnected()) {
                $adapters[$name] = $adapter;
            }
        }

        return $adapters;
    }

    /**
    * Disconnect all currently connected adapters at once
    */
    public function disconnectAllAdapters()
    {
        foreach ($this->getProviders() as $name) {
            $adapter = $this->getAdapter($name);

            if ($adapter->isConnected()) {
                $adapter->disconnect();
            }
        }
    }
}
