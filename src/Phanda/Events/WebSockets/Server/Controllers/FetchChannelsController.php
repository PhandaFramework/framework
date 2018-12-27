<?php

namespace Phanda\Events\WebSockets\Server\Controllers;

use Phanda\Events\WebSockets\Channels\UserAwareChannel;
use Phanda\Foundation\Http\Request;
use Phanda\Support\PhandaStr;

class FetchChannelsController extends AbstractWebSocketController
{

	public function __invoke(Request $request)
	{
		$channels = createDictionary(
			$this->channelManager->getApplicationChannels($request->appId)
		)->filter(function($channel) {
			return $channel instanceof UserAwareChannel;
		});

		if($request->has('filter_by_prefix')) {
			$channels = $channels->filter(function($channel, $channelName) use ($request) {
				return PhandaStr::startsIn($request->filter_by_prefix, $channelName);
			});
		}

		return [
			'channels' => $channels->map(function ($channel) {
				/** @var UserAwareChannel $channel */
				return [
					'user_count' => count($channel->getConnectedUsers()),
				];
			})->toArray() ?: new \stdClass,
		];
	}
}