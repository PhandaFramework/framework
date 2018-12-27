<?php

namespace Phanda\Events\WebSockets\Server\Controllers;

use Phanda\Foundation\Http\Request;

class SocketEventController extends AbstractWebSocketController
{
	public function __invoke(Request $request)
	{
		$this->ensureValidSignature($request);

		foreach ($request->json()->get('channels', []) as $channelName) {
			$channel = $this->channelManager->find($request->appId, $channelName);

			$channel->broadcastToAllExcept(
				$this->responseFactory->makeChannelEventResponse(
					$request->json()->get('name'),
					$channelName,
					$request->json()->get('data')
				),
				$request->json()->get('socket_id')
			);
		}

		return $request->json()->all();
	}
}