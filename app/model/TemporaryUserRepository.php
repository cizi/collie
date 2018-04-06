<?php

namespace App\Model;

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
}