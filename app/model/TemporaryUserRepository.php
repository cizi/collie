<?php

namespace App\Model;

use App\Model\Entity\DogEntity;
use App\Model\Entity\UserTemporaryEntity;

class TemporaryUserRepository extends BaseRepository {

	/**
	 * @param UserTemporaryEntity $userTemporaryEntity
	 * @return UserTemporaryEntity
	 */
	public function saveTemporaryUser(UserTemporaryEntity $userTemporaryEntity) {
		if ($userTemporaryEntity->getId() == null) {
			$query = ["insert into appdata_uzivatel_docasny ", $userTemporaryEntity->extract()];
			$this->connection->query($query);
			$userTemporaryEntity->setId($this->connection->getInsertId());
		} else {
			$updateArray = $userTemporaryEntity->extract();
			unset($updateArray['id']);
			$query = ["update appdata_uzivatel_docasny set ", $updateArray, "where id=%i", $userTemporaryEntity->getId()];
			$this->connection->query($query);
		}

		return $userTemporaryEntity;
	}

	/**
	 * @param int $id
	 * @return UserTemporaryEntity
	 */
	public function getTemporaryUserById($id) {
		$query = ["select * from appdata_uzivatel_docasny where id = %i", $id];
		$row = $this->connection->query($query)->fetch();
		if ($row) {
			$tempUser = new UserTemporaryEntity();
			$tempUser->hydrate($row->toArray());
			return $tempUser;
		}
	}

	/**
	 * @param string $name
	 * @return UserTemporaryEntity
	 */
	public function getTemporaryUserByName($name) {
		$query = ["select * from appdata_uzivatel_docasny where CeleJmeno = %s", $name];
		$row = $this->connection->query($query)->fetch();
		if ($row) {
			$tempUser = new UserTemporaryEntity();
			$tempUser->hydrate($row->toArray());
			return $tempUser;
		}
	}

	/**
	 * @param int $pID
	 * @return \Dibi\Result|int
	 */
	public function deleteAllTemporaryBreedersByDog($pID) {
		$query = ["delete from appdata_chovatel_docasny where pID = %i", $pID];
		return $this->connection->query($query);
	}

	/**
	 * @param int $pID
	 * @return \Dibi\Result|int
	 */
	public function deleteAllTemporaryOwnersByDog($pID) {
		$query = ["delete from appdata_majitel_docasny where pID = %i", $pID];
		return $this->connection->query($query);
	}

	/**
	 * Smaže dočasného uživatele a všechny jeho vazby na psy
	 * @param int $id
	 * @return bool
	 */
	public function deleteTemporaryUser($id) {
		$result = false;
		try {
			$this->connection->begin();
			$query = ["delete from appdata_chovatel_docasny where utID = %i", $id];
			$this->connection->query($query);

			$query = ["delete from appdata_majitel_docasny where utID = %i", $id];
			$this->connection->query($query);

			$query = ["delete from appdata_uzivatel_docasny where id = %i", $id];
			$this->connection->query($query);

			$result = true;
			$this->connection->commit();
		} catch (\Exception $e) {
			$this->connection->rollback();

		}

		return $result;
	}

	/**
	 * @param int $pID
	 * @return \Dibi\Result|int
	 */
	public function deleteTemporaryBreederById($id) {
		$query = ["delete from appdata_chovatel_docasny where id = %i", $id];
		return ($this->connection->query($query) == 1 ? true : false);
	}

	/**
	 * @param int $pID
	 * @return \Dibi\Result|int
	 */
	public function deleteTemporaryOwnerById($id) {
		$query = ["delete from appdata_majitel_docasny where id = %i", $id];
		return ($this->connection->query($query) == 1 ? true : false);
	}

	/**
	 * @param int $pID
	 * @return UserTemporaryEntity[]
	 */
	public function findAllTemporaryBreeders($pID) {
		$query = ["select aud.* from appdata_chovatel_docasny as acd left join appdata_uzivatel_docasny as aud on acd.utID = aud.id where acd.pID = %i", $pID];
		$result = $this->connection->query($query);

		$return = [];
		foreach($result->fetchAll() as $usr) {
			$usrEntity = new UserTemporaryEntity();
			$usrEntity->hydrate($usr->toArray());
			$return[] = $usrEntity;
		}

		return $return;
	}

	/**
	 * @param int $pID
	 * @return UserTemporaryEntity[]
	 */
	public function findAllTemporaryOwners($pID) {
		$query = ["select aud.* from appdata_majitel_docasny as amd left join appdata_uzivatel_docasny as aud on amd.utID = aud.id where amd.pID = %i", $pID];
		$result = $this->connection->query($query);

		$return = [];
		foreach($result->fetchAll() as $usr) {
			$usrEntity = new UserTemporaryEntity();
			$usrEntity->hydrate($usr->toArray());
			$return[] = $usrEntity;
		}

		return $return;
	}

	/**
	 * @param UserTemporaryEntity $userTemporaryEntity
	 * @param int $pID
	 * @return \Dibi\Result|int
	 */
	public function setTempUserAsTempBreeder(UserTemporaryEntity $userTemporaryEntity, $pID) {
		$query = ["insert into appdata_chovatel_docasny (utID, pID) values (%i, %i) ", $userTemporaryEntity->getId(), $pID];
		return $this->connection->query($query);
	}

	/**
	 * @param UserTemporaryEntity $userTemporaryEntity
	 * @param int $pID
	 * @return \Dibi\Result|int
	 */
	public function setTempUserAsTempOwner(UserTemporaryEntity $userTemporaryEntity, $pID) {
		$query = ["insert into appdata_majitel_docasny (utID, pID) values (%i, %i) ", $userTemporaryEntity->getId(), $pID];
		return $this->connection->query($query);
	}

	/**
	 * @param int $pID
	 * @return string
	 */
	public function findTemporaryBreedersAsString($pID) {
		$result = "";
		$tempBreeders = $this->findAllTemporaryBreeders($pID);
		if (count($tempBreeders) >0) {
			$result = $tempBreeders[0]->getCeleJmeno();
		}

		return $result;

	}

	/**
	 * @param int $pID
	 * @return string
	 */
	public function findTemporaryOwnersAsString($pID) {
		$result = "";
		$tempOwners = $this->findAllTemporaryOwners($pID);
		if (count($tempOwners)) {
			$tempOwnersFormString = "";
			foreach ($tempOwners as $tempOwner) {
				$tempOwnersFormString = $tempOwnersFormString . $tempOwner->getCeleJmeno() . ", ";
			}
			$result = $tempOwnersFormString;
		}

		return $result;
	}

	/**
	 * @return UserTemporaryEntity[]
	 */
	public function findAllTemporaryUsers() {
		$query = ["select * from appdata_uzivatel_docasny"];
		$result = $this->connection->query($query);

		$return = [];
		foreach($result->fetchAll() as $usr) {
			$usrEntity = new UserTemporaryEntity();
			$usrEntity->hydrate($usr->toArray());
			$return[] = $usrEntity;
		}

		return $return;
	}

	/**
	 * @param int $utID - id dočasného uživatele
	 * @return int[]
	 */
	public function findTemporaryOwnerDogs($utID) {
		$query = ["select * from appdata_majitel_docasny where utID = %i", $utID];
		$result = $this->connection->query($query);

		$dogIds = [];
		foreach($result->fetchAll() as $record) {
			$dogIds[] = $record['pID'];
		}

		return $dogIds;
	}

	/**
	 * @param int $utID - id dočasného uživatele
	 * @return int[]
	 */
	public function findTemporaryBreederDogs($utID) {
		$query = ["select * from appdata_chovatel_docasny where utID = %i", $utID];
		$result = $this->connection->query($query);

		$dogIds = [];
		foreach($result->fetchAll() as $record) {
			$dogIds[] = $record['pID'];
		}

		return $dogIds;
	}

	/**
	 * @return string
	 */
	public function findAllTemporaryUsersForDataList() {
		$query = ["select * from appdata_uzivatel_docasny"];
		$result = $this->connection->query($query);

		$return = "";
		foreach($result->fetchAll() as $usr) {
			$usrEntity = new UserTemporaryEntity();
			$usrEntity->hydrate($usr->toArray());
			$return = $return . $usrEntity->getCeleJmeno() . ",";
		}

		if (substr($return, -1) == ",") {
			$return = substr($return, 0, strlen($return) - 1);
		}

		return $return;
	}

	/**
	 * @return array - [idZáznau v tabulce] = data o psovi
	 */
	public function findRecBreedersInDogs($utID) {
		$owners = [];
		$query = ["select acd.id as acdId, ap.* from appdata_chovatel_docasny as acd left join appdata_pes as ap on acd.pID = ap.ID where acd.utID = %i", $utID];
		$result = $this->connection->query($query);

		foreach ($result->fetchAll() as $row) {
			$data = $row->toArray();
			$dog = new DogEntity();
			$dog->hydrate($data);
			$owners[$data['acdId']] = $dog;
		}

		return $owners;
	}

	/**
	 * @return array - [idZáznau v tabulce] = data o psovi
	 */
	public function findRecOwnersInDogs($utID) {
		$owners = [];
		$query = ["select amd.id as amdId, ap.* from appdata_majitel_docasny as amd left join appdata_pes as ap on amd.pID = ap.ID where amd.utID = %i", $utID];
		$result = $this->connection->query($query);

		foreach ($result->fetchAll() as $row) {
			$data = $row->toArray();
			$dog = new DogEntity();
			$dog->hydrate($data);
			$owners[$data['amdId']] = $dog;
		}

		return $owners;
	}

	/**
	 * @return int[] - idDocasnehoUzivatele
	 */
	public function findTemporaryBreeders() {
		$query = ["select distinct utID from appdata_chovatel_docasny"];
		return $this->connection->query($query)->fetchPairs("utID", "utID");
	}

	/**
	 * @return int[] - idDocasnehoUzivatele
	 */
	public function findTemporaryOwners() {
		$query = ["select distinct utID from appdata_majitel_docasny"];
		return $this->connection->query($query)->fetchPairs("utID", "utID");
	}
}