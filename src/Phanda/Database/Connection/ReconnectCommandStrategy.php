<?php

namespace Phanda\Database\Connection;

use Exception;
use Phanda\Contracts\Support\RetryCommandStrategy;

class ReconnectCommandStrategy implements RetryCommandStrategy
{
    /**
     * @var \Phanda\Contracts\Database\Connection\Connection
     */
    protected $connection;

    /**
     * The list of error strings to match when looking for a disconnection error.
     *
     * @var array
     */
    protected static $causes = [
        'gone away',
        'Lost connection',
        'Transaction() on null',
        'closed the connection unexpectedly',
        'closed unexpectedly',
        'deadlock avoided',
        'decryption failed or bad record mac',
        'is dead or not enabled',
        'no connection to the server',
        'query_wait_timeout',
        'reset by peer',
        'terminate due to client_idle_limit',
        'while sending',
        'writing data to the connection',
    ];

    /**
     * ReconnectCommandStrategy constructor.
     * @param \Phanda\Contracts\Database\Connection\Connection $connection
     */
    public function __construct(\Phanda\Contracts\Database\Connection\Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Returns whether a given command should be retried or not
     *
     * @param Exception $exception
     * @return bool
     */
    public function shouldRetry(Exception $exception): bool
    {
        $message = $exception->getMessage();

        foreach (static::$causes as $cause) {
            if (strstr($message, $cause) !== false) {
                return $this->reconnect();
            }
        }

        return false;
    }

    /**
     * Tries an reconnects a given connection
     *
     * @return bool
     */
    protected function reconnect()
    {
        if ($this->connection->inTransaction()) {
            return false;
        }

        try {
            $this->connection->disconnect();
        } catch (Exception $e) {
        }

        try {
            $this->connection->connect();
            return true;
        } catch (Exception $e) {
            return false;
        }

    }
}