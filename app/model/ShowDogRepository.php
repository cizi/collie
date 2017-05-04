<?php

namespace App\Model;

use App\Model\Entity\ShowDogEntity;

class ShowDogRepository extends BaseRepository {

	/**
	 * @param int $vID
	 * @return ShowDogEntity[]
	 */
	public function findDogsByShow($vID) {
		$query = ["select * from appdata_vystava_pes where vID = %i", $vID];
		$result = $this->connection->query($query);

		$dogs = [];
		foreach ($result->fetchAll() as $row) {
			$dog = new ShowDogEntity();
			$dog->hydrate($row->toArray());
			$dogs[] = $dog;
		}

		return $dogs;
	}

	/**
	 * @param ShowDogEntity $showDogEntity
	 */
	public function save(ShowDogEntity $showDogEntity) {
		if ($showDogEntity->getID() == null) {
			$query = ["insert into appdata_vystava_pes ", $showDogEntity->extract()];
		} else {
			$query = ["update appdata_vystava_pes set ", $showDogEntity->extract(), "where ID=%i", $showDogEntity->getID()];
		}
		$this->connection->query($query);
	}

	/**
	 * @param $id
	 * @return bool
	 */
	public function delete($id) {
		$return = false;
		if (!empty($id)) {
			$query = ["delete from appdata_vystava_pes where ID = %i", $id ];
			$return = ($this->connection->query($query) == 1 ? true : false);
		}

		return $return;
	}
}