<?php

namespace Phanda\Database\Statement;

use PDO;

class MysqlStatement extends PDOStatement
{
	/**
	 * Whether or not to buffer results in php
	 *
	 * @var bool
	 */
	protected $bufferResults = true;

	/**
	 * Whether or not to buffer results in php
	 *
	 * @param bool $buffer Toggle buffering
	 * @return $this
	 */
	public function setBufferResults(bool $buffer)
	{
		$this->bufferResults = $buffer;

		return $this;
	}

    /**
     * {@inheritDoc}
     *
     */
    public function execute($params = null): bool
    {
        $connection = $this->driver->getConnection();

        try {
            $connection->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, $this->bufferResults);
            $connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $result = $this->statement->execute($params);
        } finally {
            $connection->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
        }

        $this->executed = true;
        return $result;
    }
}