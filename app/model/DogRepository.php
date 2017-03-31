<?php

namespace App\Model;

use App\Model\Entity\DogEntity;
use App\Model\Entity\DogHealthEntity;
use App\Model\Entity\DogPicEntity;
use Dibi\Exception;
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
		$return = true;
		if (!empty($id)) {
			try {
				$this->connection->begin();

				$query = ["delete from appdata_pes_obrazky where pID = %i", $id];    // nejdøíve smau obrázky
				$this->connection->query($query);

				// TODO zdravi

				$query = ["delete from appdata_pes where ID = %i", $id];    // pak smau psa
				$this->connection->query($query);

				$this->connection->commit();
			} catch (\Exception $e) {
				$this->connection->rollback();
				$return = false;
			}
		}

		return $return;
	}

	/**
	 * @param int $typ
	 * @param int $pID
	 * @return DogHealthEntity
	 */
	public function getHealthEntityByDogAndType($typ, $pID) {
		$query = ["select * from appdata_zdravi where Typ = %i and pID = %i", $typ, $pID];
		$row = $this->connection->query($query)->fetch();
		if ($row) {
			$healthEntity = new DogHealthEntity();
			$healthEntity->hydrate($row->toArray());
			return $healthEntity;
		}
	}

	/**
	 * @param DogEntity $dogEntity
	 * @param DogPicEntity []
	 */
	public function save(DogEntity $dogEntity, $dogPics, array $dogHealth) {
		$dataArray = $dogEntity->extract();
		$dataArray['PosledniZmena'] = new DateTime();

		try {
			$this->connection->begin();
			if ($dogEntity->getID() == null) {	// novı pes
				$query = ["insert into appdata_pes ", $dataArray];
				$this->connection->query($query);
				$dogEntity->setID($this->connection->getInsertId());
			} else {	// editovanı pes
				$query = ["update appdata_pes set ", $dataArray, "where ID=%i", $dogEntity->getID()];
				$this->connection->query($query);
			}
			/** @var DogHealthEntity $dogHealthEntity */
			foreach($dogHealth as $dogHealthEntity) {
				$dogHealthEntity->setPID($dogEntity->getID());
				if ($dogHealthEntity->getID() == null) {
					$query = ["insert into appdata_zdravi ", $dogHealthEntity->extract()];
				} else {
					$query = ["update appdata_zdravi set ", $dogHealthEntity->extract(), "where ID=%i", $dogHealthEntity->getID()];
				}
				$this->connection->query($query);
			}

			/** @var DogPicEntity $dogPic */
			foreach ($dogPics as $dogPic) {
				$dogPic->setPID($dogEntity->getID());
				$dogPic->setVychozi(0);
				$picQuery = $query = ["insert into appdata_pes_obrazky ", $dogPic->extract()];
				$this->connection->query($picQuery);
			}
			$this->connection->commit();
		} catch (\Exception $e) {
			$this->connection->rollback();
		}
	}

	/**
	 * @param int $pID
	 * @return DogPicEntity[]
	 */
	public function findDogPics($pID) {
		$query = ["select * from appdata_pes_obrazky where pID = %i", $pID];
		$result = $this->connection->query($query);

		$pics = [];
		foreach ($result->fetchAll() as $row) {
			$dogPic = new DogPicEntity();
			$dogPic->hydrate($row->toArray());
			$pics[] = $dogPic;
		}

		return $pics;
	}

	/**
	 * @param int $dogId
	 * @param int $picId
	 */
	public function setDefaultDogPic($dogId, $picId) {
		$query = ["update appdata_pes_obrazky set vychozi=0 where pID = %i", $dogId];
		$this->connection->query($query);
		$query = ["update appdata_pes_obrazky set vychozi=1 where pID = %i and id = %i", $dogId, $picId];
		$this->connection->query($query);
	}

	/**
	 * @param int $id
	 * @return bool
	 */
	public function deleteDogPic($id) {
		$return = true;
		if (!empty($id)) {
			$query = ["delete from appdata_pes_obrazky where id = %i", $id];
			$return = $this->connection->query($query) == 1 ? true : false;
		}

		return $return;
	}
}