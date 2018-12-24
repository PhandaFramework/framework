<?php

namespace Phanda\Contracts\Events\WebSockets\Application;

interface SocketApp
{

	/**
	 * Gets the internal ID of the application
	 *
	 * @return int
	 */
	public function getAppId(): int;

	/**
	 * Sets the internal ID of the application
	 *
	 * @param int $id
	 * @return SocketApp
	 */
	public function setAppId(int $id): SocketApp;

	/**
	 * Gets the internal key of the application
	 *
	 * @return string
	 */
	public function getAppKey(): string;

	/**
	 * Sets the internal key of the application
	 *
	 * @param string $key
	 * @return SocketApp
	 */
	public function setAppKey(string $key): SocketApp;

	/**
	 * Gets the internal secret key of the application
	 *
	 * @return string
	 */
	public function getAppSecret(): string;

	/**
	 * Sets the internal secret key of the application
	 *
	 * @param string $secret
	 * @return SocketApp
	 */
	public function setAppSecret(string $secret): SocketApp;

	/**
	 * Gets the host of the application
	 *
	 * @return string
	 */
	public function getAppHost(): string;

	/**
	 * Sets the host of the application
	 *
	 * @param string $host
	 * @return SocketApp
	 */
	public function setAppHost(string $host): SocketApp;

	/**
	 * @param string $appName
	 * @return SocketApp
	 */
	public function setAppName(string $appName): SocketApp;

	/**
	 * @return string
	 */
	public function getAppName(): string;

}