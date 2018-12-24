<?php

namespace Phanda\Events\WebSockets\Application;

use Phanda\Contracts\Events\WebSockets\Application\SocketApp as SocketAppContract;

class SocketApp implements SocketAppContract
{
	/**
	 * The id of the application
	 *
	 * @var int
	 */
	protected $appId;

	/**
	 * The name of the application
	 *
	 * @var string
	 */
	protected $appName;

	/**
	 * The public key of the application
	 *
	 * @var string
	 */
	protected $appKey;

	/**
	 * The secret key of the application
	 *
	 * @var string
	 */
	protected $appSecret;

	/**
	 * The host of the application
	 *
	 * @var string
	 */
	protected $appHost;

	/**
	 * SocketApp constructor.
	 *
	 * @param int    $appId
	 * @param string $appKey
	 * @param string $appSecret
	 */
	public function __construct(int $appId, string $appKey, string $appSecret)
	{
		$this->setAppId($appId);
		$this->setAppKey($appKey);
		$this->setAppSecret($appSecret);
	}

	/**
	 * Gets the internal ID of the application
	 *
	 * @return int
	 */
	public function getAppId(): int
	{
		return $this->appId;
	}

	/**
	 * Sets the internal ID of the application
	 *
	 * @param int $id
	 * @return SocketAppContract
	 */
	public function setAppId(int $id): SocketAppContract
	{
		$this->appId = $id;
		return $this;
	}

	/**
	 * Gets the internal key of the application
	 *
	 * @return string
	 */
	public function getAppKey(): string
	{
		return $this->appKey;
	}

	/**
	 * Sets the internal key of the application
	 *
	 * @param string $key
	 * @return SocketAppContract
	 */
	public function setAppKey(string $key): SocketAppContract
	{
		$this->appKey = $key;
		return $this;
	}

	/**
	 * Gets the internal secret key of the application
	 *
	 * @return string
	 */
	public function getAppSecret(): string
	{
		return $this->appSecret;
	}

	/**
	 * Sets the internal secret key of the application
	 *
	 * @param string $secret
	 * @return SocketAppContract
	 */
	public function setAppSecret(string $secret): SocketAppContract
	{
		$this->appSecret = $secret;
		return $this;
	}

	/**
	 * Gets the host of the application
	 *
	 * @return string
	 */
	public function getAppHost(): string
	{
		return $this->appHost;
	}

	/**
	 * Sets the host of the application
	 *
	 * @param string $host
	 * @return SocketAppContract
	 */
	public function setAppHost(string $host): SocketAppContract
	{
		$this->appHost = $host;
		return $this;
	}

	/**
	 * @param string $appName
	 * @return SocketAppContract
	 */
	public function setAppName(string $appName): SocketAppContract
	{
		$this->appName = $appName;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getAppName(): string
	{
		return $this->appName;
	}
}