<?php

namespace Phanda\Contracts\Events\WebSockets\Messages;

interface Message
{
	/**
	 * Responds to a message
	 *
	 * @return void
	 */
	public function respond();
}