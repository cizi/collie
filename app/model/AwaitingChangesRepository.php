<?php

namespace App\Model;

use App\Enum\DogChangeStateEnum;
use App\Model\Entity\AwaitingChangesEntity;
use Dibi\DateTime;
use Dibi\Exception;
use Nette\Security\User;

class AwaitingChangesRepository extends BaseRepository {

	/**
	 * @return AwaitingChangesEntity[]
	 */
	public function findAwaitingChanges() {
			$query = ["select * from appdata_zmeny where stav= %i order by datimVlozeno asc", DogChangeStateEnum::INSERTED];
			$result = $this->connection->query($query);

			$awaitingChanges = [];
			foreach ($result->fetchAll() as $row) {
				$change = new AwaitingChangesEntity();
				$change->hydrate($row->toArray());
				$awaitingChanges[] = $change;
			}

			return $awaitingChanges;
	}

	/**
	 * @return AwaitingChangesEntity[]
	 */
	public function findProceededChanges() {
		$query = ["select * from appdata_zmeny where stav= %i order by datimVlozeno asc", DogChangeStateEnum::PROCEEDED];
		$result = $this->connection->query($query);

		$awaitingChanges = [];
		foreach ($result->fetchAll() as $row) {
			$change = new AwaitingChangesEntity();
			$change->hydrate($row->toArray());
			$awaitingChanges[] = $change;
		}

		return $awaitingChanges;
	}

	/**
	 * @return AwaitingChangesEntity[]
	 */
	public function findDeclinedChanges() {
		$query = ["select * from appdata_zmeny where stav= %i order by datimVlozeno asc", DogChangeStateEnum::DECLINED];
		$result = $this->connection->query($query);

		$awaitingChanges = [];
		foreach ($result->fetchAll() as $row) {
			$change = new AwaitingChangesEntity();
			$change->hydrate($row->toArray());
			$awaitingChanges[] = $change;
		}

		return $awaitingChanges;
	}

	/**
	 * @param int $id
	 * @return AwaitingChangesEntity
	 */
	public function getAwaitingChange($id) {
		$query = ["select * from appdata_zmeny where ID = %i", $id];
		$row = $this->connection->query($query)->fetch();
		if ($row) {
			$awaitingEntity = new AwaitingChangesEntity();
			$awaitingEntity->hydrate($row->toArray());
			return $awaitingEntity;
		}
	}

	/**
	 * @param AwaitingChangesEntity $awaitChngEnt
	 * @param User $user
	 */
	public function proceedChange(AwaitingChangesEntity $awaitChngEnt, User $user) {
		$this->connection->begin();
		try {
			// zapíšu změny do tabulky
			$idColumn = ($awaitChngEnt->getTabulka() == "appdata_pes_soubory" ? "id" : "ID");	// srovnání názvu sloupců ID podle tabulky
			$query = ["update {$awaitChngEnt->getTabulka()} set `{$awaitChngEnt->getSloupec()}` = '{$awaitChngEnt->getPozadovanaHodnota()}' where `{$idColumn}` = '{$awaitChngEnt->getPID()}'"];
			$this->connection->query($query);

			// aktualizuji záznam změny
			$awaitChngEnt->setDatimZpracovani(new DateTime());
			$awaitChngEnt->setStav(DogChangeStateEnum::PROCEEDED);
			$awaitChngEnt->setUIDKdoSchvalil($user->getId());
			$query = ["update appdata_zmeny set ", $awaitChngEnt->extract(), "where ID=%i", $awaitChngEnt->getID()];
			$this->connection->query($query);
		} catch (\Exception $e) {
			$this->connection->rollback();
			throw $e;
		}
		$this->connection->commit();
	}

	/**
	 * @param AwaitingChangesEntity $awaitingChangesEntity
	 * @param User $user
	 */
	public function declineChange(AwaitingChangesEntity $awaitingChangesEntity, User $user) {
		$awaitingChangesEntity->setDatimZpracovani(new DateTime());
		$awaitingChangesEntity->setStav(DogChangeStateEnum::DECLINED);
		$awaitingChangesEntity->setUIDKdoSchvalil($user->getId());
		$query = ["update appdata_zmeny set ", $awaitingChangesEntity->extract(), "where ID=%i", $awaitingChangesEntity->getID()];
		$this->connection->query($query);
	}

	/**
	 * @param int $id
	 * @return bool
	 */
	public function deleteAwaitingChange($id) {
		$return = false;
		if (!empty($id)) {
			$query = ["delete from appdata_zmeny where ID = %i", $id];
			$return = ($this->connection->query($query) == 1 ? true : false);
		}

		return $return;
	}

	/**
	 * @param AwaitingChangesEntity[] $dogAwaitingChangesEntities
	 */
	public function writeDogChanges(array $dogAwaitingChangesEntities) {
		$this->connection->begin();
		try {
			foreach ($dogAwaitingChangesEntities as $dogChangeEnt) {
				$this->save($dogChangeEnt);
			}
		} catch (\Exception $e) {
			$this->connection->rollback();
		}
		$this->connection->commit();
	}

	/**
	 * Zapíše poždavek do tabulky
	 * @param AwaitingChangesEntity $awaitingChangesEntity
	 */
	private function save(AwaitingChangesEntity $awaitingChangesEntity) {
		$query = ["insert into appdata_zmeny", $awaitingChangesEntity->extract()];
		$this->connection->query($query);
	}
}