<?php

namespace App\Model;

use App\Model\Entity\DogEntity;
use Nette\Utils\DateTime;

class DogRepository extends BaseRepository {

	/**
	 * @param int $id
	 * @return DogEntity
	 */
	public function getDog($id) {
		$query = ["select * from appdata_pes where ID = %i", $id];
		$row = $this->connection->query($query)->fetch();
		if ($row) {
			$dogEntity = new DogEntity();
			$dogEntity->hydrate($row->toArray());
			return $dogEntity;
		}
	}

	/**
	 * @return DogEntity[]
	 */
	public function findDogs() {
		$query = "select * from appdata_pes";
		$result = $this->connection->query($query);

		$dogs = [];
		foreach ($result->fetchAll() as $row) {
			$dog = new DogEntity();
			$dog->hydrate($row->toArray());
			$dogs[] = $dog;
		}

		return $dogs;
	}

	/**
	 * @param int $id
	 * @return bool
	 */
	public function delete($id) {
		$return = false;
		if (!empty($id)) {
			$query = ["delete from appdata_pes where ID = %i", $id ];
			$return = ($this->connection->query($query) == 1 ? true : false);
		}

		return $return;
	}

	/**
	 * @param DogEntity $dogEntity
	 * @param DogPicEntity[]
	 */
	public function save(DogEntity $dogEntity, array $dogPics) {
		$dataArray = $dogEntity->extract();
		$dataArray['PosledniZmena'] = new DateTime();
		if ($dogEntity->getID() == null) {
			$query = ["insert into appdata_pes ", $dataArray];
		} else {
			$query = ["update appdata_pes set ", $dataArray, "where id=%i", $dogEntity->getID()];
		}

		$this->connection->query($query);
	}

	public function saveDogPic($id, $path) {


	}
}