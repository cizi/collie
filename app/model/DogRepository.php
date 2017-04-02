<?php

namespace App\Model;

use App\Model\Entity\BreederEntity;
use App\Model\Entity\DogEntity;
use App\Model\Entity\DogHealthEntity;
use App\Model\Entity\DogOwnerEntity;
use App\Model\Entity\DogPicEntity;
use Dibi\Exception;
use Nette\Utils\DateTime;

class DogRepository extends BaseRepository {

	/** @const znak pro nevybraného psa v selectu  */
	const NOT_SELECTED = "-";

	/** @const poøadí pro fenu */
	const FEMALE_ORDER = 30;

	/** @const poøadí pro psa */
	const MALE_ORDER = 29;

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
	 * @return array
	 */
	public function findFemaleDogsForSelect() {
		$query = ["select * from appdata_pes where Pohlavi = %i", self::FEMALE_ORDER];
		$result = $this->connection->query($query);

		$dogs[0] = self::NOT_SELECTED;
		foreach ($result->fetchAll() as $row) {
			$dog = new DogEntity();
			$dog->hydrate($row->toArray());
			$dogs[$dog->getID()] = $dog->getTitulyPredJmenem() . " " . $dog->getJmeno() . " " . $dog->getTitulyZaJmenem();
		}

		return $dogs;
	}

	/**
	 * @return DogEntity[]
	 */
	public function findMaleDogsForSelect() {
		$query = ["select * from appdata_pes where Pohlavi = %i", self::MALE_ORDER];
		$result = $this->connection->query($query);

		$dogs[0] = self::NOT_SELECTED;
		foreach ($result->fetchAll() as $row) {
			$dog = new DogEntity();
			$dog->hydrate($row->toArray());
			$dogs[$dog->getID()] = $dog->getTitulyPredJmenem() . " " . $dog->getJmeno() . " " . $dog->getTitulyZaJmenem();
		}

		return $dogs;
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

				$this->deleteHealthByDogId($id);
				$this->deleteBreederByDogId($id);
				$this->deleteOwnerByDogId($id);

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
	 * @param int $pID
	 */
	private function deleteHealthByDogId($pID) {
		$query = ["delete from appdata_zdravi where pID = %i", $pID];
		$this->connection->query($query);
	}

	/**
	 * @param int $pID
	 */
	private function deleteBreederByDogId($pID) {
		$query = ["delete from appdata_chovatel where pID = %i", $pID];
		$this->connection->query($query);
	}

	/**
	 * @param int $pID
	 */
	private function deleteOwnerByDogId($pID) {
		$query = ["delete from appdata_majitel where pID = %i", $pID];
		$this->connection->query($query);
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
	 * @param DogPicEntity[]
	 * @param DogHealthEntity[]
	 * @param BreederEntity[]
	 * @param DogOwnerEntity[]
	 */
	public function save(DogEntity $dogEntity, array $dogPics, array $dogHealth, array $breeders, array $owners) {
		try {
			$this->connection->begin();
			$dogEntity->setPosledniZmena(new DateTime());
			if ($dogEntity->getMID() == 0) {
				$dogEntity->setMID(null);
			}
			if ($dogEntity->getOID() == 0) {
				$dogEntity->setOID(null);
			}
			if ($dogEntity->getID() == null) {	// novı pes
				$query = ["insert into appdata_pes ", $dogEntity->extract()];
				$this->connection->query($query);
				$dogEntity->setID($this->connection->getInsertId());
			} else {	// editovanı pes
				$query = ["update appdata_pes set ", $dogEntity->extract(), "where ID=%i", $dogEntity->getID()];
				$this->connection->query($query);
			}
			/** @var DogHealthEntity $dogHealthEntity */
			foreach($dogHealth as $dogHealthEntity) {
				$dogHealthEntity->setPID($dogEntity->getID());
				if ($dogHealthEntity->getVeterinar() == 0) {	// pokud nebyl veterináø vybrán vynuluji jeho záznam
					$dogHealthEntity->setVeterinar(null);
				}
				if ($dogHealthEntity->getID() == null) {
					$query = ["insert into appdata_zdravi ", $dogHealthEntity->extract()];
				} else {
					$query = ["update appdata_zdravi set ", $dogHealthEntity->extract(), "where ID=%i", $dogHealthEntity->getID()];
				}
				$this->connection->query($query);
			}
			/** @var BreederEntity $breeder */
			foreach($breeders as $breeder) {
				$breeder->setPID($dogEntity->getID());
				if ($breeder->getUID() == 0) {		// pokud je v selectu vybrána nula tak mau
					$this->deleteBreederByDogId($dogEntity->getID());
				} else {
					$query = ($breeder->getID() == null ? ["insert into appdata_chovatel ", $breeder->extract()] : ["update appdata_chovatel set ", $breeder->extract(), "where ID=%i", $breeder->getID()]);
					$this->connection->query($query);
				}
			}

			$query = ["update appdata_majitel set Soucasny = %i where pID = %i", 0, $dogEntity->getID()];	// nevím co mi nyní pøijde take všechny rovnou udìlám jako bıvalé majitele
			$this->connection->query($query);
			/** @var DogOwnerEntity $owner */
			foreach($owners as $owner) {
				$owner->setPID($dogEntity->getID());
				$alreadyIn = ["select * from appdata_majitel where uID = %i and pID = %i", $owner->getUID(), $dogEntity->getID()];
				$row = $this->connection->query($alreadyIn)->fetch();
				if ($row) {	// pokud existuje akorat pøepnu na souèasného
					$dogOwn = new DogOwnerEntity();
					$dogOwn->hydrate($row->toArray());
					$query = ["update appdata_majitel set Soucasny = %i where ID = %i", $owner->isSoucasny(), $dogOwn->getID()];
				} else {	// pokud záznam neexistuje vloím jako novı souèasnı majitel
					$query = ["insert into appdata_majitel ", $owner->extract()];
				}
				$this->connection->query($query);
			}

			/** @var DogPicEntity $dogPic */
			foreach ($dogPics as $dogPic) {
				$dogPic->setPID($dogEntity->getID());
				$dogPic->setVychozi(0);
				$picQuery = ["insert into appdata_pes_obrazky ", $dogPic->extract()];
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