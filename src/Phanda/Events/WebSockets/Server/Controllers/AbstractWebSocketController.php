<?php

namespace Phanda\Events\WebSockets\Server\Controllers;

use GuzzleHttp\Psr7\ServerRequest;
use Phanda\Contracts\Events\WebSockets\Channels\Manager as ChannelManager;
use Phanda\Events\WebSockets\Data\ResponseFactory;
use Phanda\Events\WebSockets\Manager;
use Phanda\Exceptions\Events\WebSockets\InvalidAppIdException;
use Phanda\Exceptions\Events\WebSockets\InvalidAppKeyException;
use Phanda\Exceptions\Events\WebSockets\InvalidSocketSignature;
use Phanda\Foundation\Http\Request;
use Psr\Http\Message\RequestInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Http\HttpServerInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Component\HttpKernel\Exception\HttpException;

abstract class AbstractWebSocketController implements HttpServerInterface
{
	/**
	 * @var Manager
	 */
	protected $manager;

	/**
	 * @var ChannelManager
	 */
	protected $channelManager;

	/**
	 * @var ResponseFactory
	 */
	protected $responseFactory;

	/**
	 * AbstractWebSocketController constructor.
	 *
	 * @param Manager        $manager
	 * @param ChannelManager $channelManager
	 */
	public function __construct(Manager $manager, ChannelManager $channelManager)
	{
		$this->manager = $manager;
		$this->channelManager = $channelManager;
		$this->responseFactory = phanda()->create(ResponseFactory::class);
	}

	/**
	 * This is called before or after a socket is closed (depends on how it's closed).  SendMessage to $conn will not
	 * result in an error if it has already been closed.
	 *
	 * @param  ConnectionInterface $conn The socket/connection that is closing/closed
	 * @throws \Exception
	 */
	function onClose(ConnectionInterface $conn)
	{
	}

	/**
	 * Triggered when a client sends data through the socket
	 *
	 * @param  \Ratchet\ConnectionInterface $from The socket/connection that sent the message to your application
	 * @param  string                       $msg  The message received
	 * @throws \Exception
	 */
	function onMessage(ConnectionInterface $from, $msg)
	{
	}

	/**
	 * If there is an error with one of the sockets, or somewhere in the application where an Exception is thrown,
	 * the Exception is sent back down the stack, handled by the Server and bubbled back up the application through
	 * this method
	 *
	 * @param ConnectionInterface $connection
	 * @param  \Exception         $e
	 */
	function onError(ConnectionInterface $connection, \Exception $e)
	{
		if (!$e instanceof HttpException) {
			return;
		}

		$response = responseManager()->createJsonResponse(['error' => $e->getMessage()], $e->getStatusCode());

		$connection->send($response);
		$connection->close();
	}

	/**
	 * @param ConnectionInterface                $connection
	 * @param \Psr\Http\Message\RequestInterface $request
	 */
	public function onOpen(ConnectionInterface $connection, RequestInterface $request = null)
	{
		$params = [];
		parse_str($request->getUri()->getQuery(), $params);

		$serverRequest = (new ServerRequest(
			$request->getMethod(),
			$request->getUri(),
			$request->getHeaders(),
			$request->getBody(),
			$request->getProtocolVersion()
		))->withQueryParams($params);

		$phandaRequest = Request::createFromSymfonyRequest((new HttpFoundationFactory)->createRequest($serverRequest));

		$this
			->verifyAppId($phandaRequest->appId)
			->ensureValidSignature($phandaRequest);

		$response = $this($phandaRequest);

		$connection->send(responseManager()->createJsonResponse($response));
		$connection->close();
	}

	/**
	 * Verifies that an application with an id exists
	 *
	 * @param string $appId
	 * @return $this
	 */
	public function verifyAppId(string $appId)
	{
		if (!$this->manager->isAppIdRegistered($appId)) {
			throw new InvalidAppIdException($appId);
		}

		return $this;
	}

	/**
	 * Ensures a request has a valid signature
	 *
	 * @param Request $request
	 * @return $this
	 */
	protected function ensureValidSignature(Request $request)
	{
		$signature =
			"{$request->getMethod()}\n/{$request->path()}\n" .
			"auth_key={$request->get('auth_key')}" .
			"&auth_timestamp={$request->get('auth_timestamp')}" .
			"&auth_version={$request->get('auth_version')}";

		if ($request->getContent() !== '') {
			$bodyMd5 = md5($request->getContent());

			$signature .= "&body_md5={$bodyMd5}";
		}

		$authSignature = hash_hmac('sha256', $signature, $this->manager->getApplicationById($request->get('appId'))->getAppSecret());

		if ($authSignature !== $request->get('auth_signature')) {
			throw new InvalidSocketSignature('Invalid authentication signature provided.');
		}

		return $this;
	}

	abstract public function __invoke(Request $request);
}