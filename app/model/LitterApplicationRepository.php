<?php

namespace App\Model;

use App\Model\Entity\LitterApplicationEntity;

class LitterApplicationRepository extends BaseRepository {

	/**
	 * @return LitterApplicationEntity[]
	 */
	public function findLitterApplications() {
		$query = "select * from appdata_krycilist order by Datum desc";
		$result = $this->connection->query($query);

		$applications = [];
		foreach ($result->fetchAll() as $row) {
			$application = new LitterApplicationEntity();
			$application->hydrate($row->toArray());
			$applications[] = $application;
		}

		return $applications;
	}

	/**
	 * @param LitterApplicationEntity $litterApplicationEntity
	 */
	public function save(LitterApplicationEntity $litterApplicationEntity) {
		if ($litterApplicationEntity->getID() == null) {
			$query = ["insert into appdata_krycilist ", $litterApplicationEntity->extract()];
			$this->connection->query($query);
			$litterApplicationEntity->setID($this->connection->getInsertId());
		} else {
			$query = ["update appdata_krycilist set ", $litterApplicationEntity->extract(), "where ID=%i", $litterApplicationEntity->getID()];
			$this->connection->query($query);
		}
	}

	/**
	 * @param int $id
	 * @return LitterApplicationEntity
	 */
	public function getLitterApplication($id) {
		$query = ["select * from appdata_krycilist where ID = %i", $id];
		$row = $this->connection->query($query)->fetch();
		if ($row) {
			$litterApplicationEntity = new LitterApplicationEntity();
			$litterApplicationEntity->hydrate($row->toArray());
			return $litterApplicationEntity;
		}
	}

	/**
	 * @param int $id
	 * @return bool
	 */
	public function delete($id) {
		$return = false;
		if (!empty($id)) {
			$query = ["delete from appdata_krycilist where ID = %i", $id];
			$return = ($this->connection->query($query) == 1 ? true : false);
		}

		return $return;
	}
}