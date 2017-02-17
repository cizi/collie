<?php

namespace App\Model;

class BaseRepository extends \Nette\Object {

	/** @var \Dibi\Connection */
	protected $connection;

	public function __construct(\Dibi\Connection $connection) {
		$this->connection = $connection;
	}
}