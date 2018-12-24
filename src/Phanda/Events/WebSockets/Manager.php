<?php

namespace Phanda\Events\WebSockets;

use Phanda\Contracts\Events\WebSockets\Application\SocketApp as SocketAppContract;
use Phanda\Events\WebSockets\Application\SocketApp;
use Phanda\Exceptions\Events\WebSockets\SocketApplicationException;

class Manager
{
	/**
	 * The internal array of all active socket applications
	 *
	 * @var SocketAppContract[]
	 */
	protected $socketApps = [];

	/**
	 * Manager constructor.
	 *
	 * @param array|null $config
	 */
	public function __construct(?array $config = null)
	{
		if (!is_null($config)) {
			$this->loadApplicationsFromConfig($config);
		}
	}

	/**
	 * Loads applications from a configuration array
	 *
	 * @param array $config
	 */
	protected function loadApplicationsFromConfig(array $config)
	{
		foreach ($config as $app) {
			if (isset($app['id']) && isset($app['key']) && isset($app['secret'])) {
				$this->registerApplication(
					$app['id'],
					$app['key'],
					$app['secret'],
					$app['name'] ?? null,
					$app['host'] ?? null
				);
			}
		}
	}

	/**
	 * Registers a Socket Application with the manager
	 *
	 * @param int         $appId
	 * @param string      $appKey
	 * @param string      $appSecret
	 * @param string|null $appName
	 * @param string|null $appHost
	 * @return $this
	 *
	 * @throws SocketApplicationException
	 */
	public function registerApplication(int $appId, string $appKey, string $appSecret, ?string $appName = null, ?string $appHost = null): Manager
	{
		$this->verifyApplicationUnique($appId, $appKey);
		$application = new SocketApp($appId, $appKey, $appSecret);

		if ($appName) {
			$application->setAppName($appName);
		}

		iF ($appHost) {
			$application->setAppHost($appHost);
		}

		$this->socketApps[] = $application;
		return $this;
	}

	/**
	 * Verifies an application is unique
	 *
	 * @param int    $appId
	 * @param string $appKey
	 *
	 * @throws SocketApplicationException
	 */
	protected function verifyApplicationUnique(int $appId, string $appKey)
	{
		if ($this->isAppIdRegistered($appId)) {
			throw SocketApplicationException::makeFromUniqueId($appId);
		}

		if ($this->isAppKeyRegistered($appKey)) {
			throw SocketApplicationException::makeFromUniqueKey($appKey);
		}
	}

	/**
	 * Gets an application by its ID
	 *
	 * @param int $appId
	 * @return SocketAppContract|null
	 */
	public function getApplicationById(int $appId): ?SocketAppContract
	{
		foreach ($this->socketApps as $app) {
			if ($app->getAppId() == $appId) {
				return $app;
			}
		}

		return null;
	}

	/**
	 * Gets an application by its key
	 *
	 * @param string $appKey
	 * @return SocketAppContract|null
	 */
	public function getApplicationByKey(string $appKey): ?SocketAppContract
	{
		foreach ($this->socketApps as $app) {
			if ($app->getAppKey() == $appKey) {
				return $app;
			}
		}

		return null;
	}

	/**
	 * Checks if an application id has been registered
	 *
	 * @param int $appId
	 * @return bool
	 */
	public function isAppIdRegistered(int $appId): bool
	{
		return $this->getApplicationById($appId) !== null;
	}

	/**
	 * Checks if an application key has been registered
	 *
	 * @param string $appKey
	 * @return bool
	 */
	public function isAppKeyRegistered(string $appKey): bool
	{
		return $this->getApplicationByKey($appKey) !== null;
	}
}