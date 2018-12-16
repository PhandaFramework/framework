<?php

namespace Phanda\Providers\Bear;

use Phanda\Bear\Table\Table;
use Phanda\Contracts\Bear\Table\TableRepository;
use Phanda\Providers\AbstractServiceProvider;

use Phanda\Contracts\Bear\Query\Builder as QueryBuilderContact;
use Phanda\Bear\Query\Builder as QueryBuilder;

class BearServiceProvider extends AbstractServiceProvider
{

	/**
	 * Registers the BearORM and it's related classes
	 */
	public function register()
	{
		$this->registerBearQueryBuilder();
		$this->registerBearTable();
	}

	/**
	 * Registers the bear QueryBuilder, so that it can resolve whenever it is type hinted.
	 */
	public function registerBearQueryBuilder()
	{
		$this->phanda->attach(QueryBuilderContact::class, QueryBuilder::class);
	}

	/**
	 * Registers the Bear Table, so that it resolves whenever it is type hinted.
	 */
	public function registerBearTable()
	{
		$this->phanda->attach(TableRepository::class, function () {
			return new Table();
		});

		$this->phanda->alias(TableRepository::class, Table::class);
	}

}