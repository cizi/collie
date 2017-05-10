<?php

namespace App\Model;

use App\Model\Entity\ShowRefereeEntity;

class ShowRefereeRepository extends BaseRepository {

	/**
	 * @param int $vID
	 * @return ShowRefereeEntity[]
	 */
	public function findRefereeByShow($vID) {
		$query = ["select * from appdata_vystava_rozhodci where vID = %i", $vID];
		$result = $this->connection->query($query);

		$referees = [];
		foreach ($result->fetchAll() as $row) {
			$referee = new ShowRefereeEntity();
			$referee->hydrate($row->toArray());
			$referees[] = $referee;
		}

		return $referees;
	}

	/**
	 * @param ShowRefereeEntity $showRefereeEntity
	 */
	public function save(ShowRefereeEntity $showRefereeEntity) {
		if ($showRefereeEntity->getID() == null) {
			$query = ["insert into appdata_vystava_rozhodci ", $showRefereeEntity->extract()];
		} else {
			$query = ["update appdata_vystava_rozhodci set ", $showRefereeEntity->extract(), "where ID=%i", $showRefereeEntity->getID()];
		}
		$this->connection->query($query);
	}

	/**
	 * @param ShowRefereeEntity $showRefereeEntity
	 * @return bool
	 */
	public function existsRefereeForShowClassBreed(ShowRefereeEntity $showRefereeEntity) {
		$query = ["select * from appdata_vystava_rozhodci where vID = %i and rID = %i and Trida = %i and Plemeno = %i",
			$showRefereeEntity->getVID(),
			$showRefereeEntity->getRID(),
			$showRefereeEntity->getTrida(),
			$showRefereeEntity->getPlemeno()
		];

		return (count($this->connection->query($query)->fetchAll()) ? true : false);
	}

	/**
	 * @param array $refereees
	 */
	public function saveReferees(array $refereees) {
		try {
			$this->connection->begin();
			/** @var ShowRefereeEntity $referee */
			foreach ($refereees as $referee) {
				if ($this->existsRefereeForShowClassBreed($referee) == false) {
					$this->save($referee);
				}
			}
			$this->connection->commit();
		} catch (\Exception $e) {
			$this->connection->rollback();
			throw $e;
		}
	}

	/**
	 * @param $id
	 * @return bool
	 */
	public function delete($id) {
		$return = false;
		if (!empty($id)) {
			$query = ["delete from appdata_vystava_rozhodci where ID = %i", $id ];
			$return = ($this->connection->query($query) == 1 ? true : false);
		}

		return $return;
	}

	/**
	 * @param int $vID
	 * @param int $rID
	 * @return bool
	 */
	public function deleteByVIDAndRID($vID, $rID) {
		$return = false;
		if (!empty($vID) && !empty($rID)) {
			$query = ["delete from appdata_vystava_rozhodci where vID = %i and rID = %i", $vID, $rID];
			$return = ($this->connection->query($query) == 1 ? true : false);
		}

		return $return;
	}

	public function migrateRefereeFromOldStructure() {
		$result = $this->connection->query("select * from v2j");
		$showRepository = new ShowRepository($this->connection);
		$refereeRepositoty = new RefereeRepository($this->connection);
		try {
			$this->connection->begin();
			foreach ($result->fetchAll() as $row) {
				$showRefereeEntity = new ShowRefereeEntity();

				if ($showRepository->getShow($row['vID']) == null) {    // v DB chybí odkaz na výstavu = nemùžu zmigrovat
					continue;
				}
				$showRefereeEntity->setVID($row['vID']);

				if ($refereeRepositoty->getReferee($row['jID']) == null) { // pokud v DB chybí odkaz na rozhodciho nemuzu to migrovat
					continue;
				}
				$showRefereeEntity->setRID($row['jID']);

				$plemenaStara = ["p1" => 18, "p2" => 17, "p3" => 19];
				foreach ($plemenaStara as $stary => $novy) {
					if ($row[$stary] == 1) {
						$showRefereeEntity->setPlemeno($novy);
						if ($row['t10'] == 1) {
							$showRefereeEntity->setTrida(102);
							$this->save($showRefereeEntity);
						}
						if ($row['t20'] == 1) {
							$showRefereeEntity->setTrida(103);
							$this->save($showRefereeEntity);
						}
						if ($row['t30'] == 1) {
							$showRefereeEntity->setTrida(104);
							$this->save($showRefereeEntity);
						}
						if ($row['t40'] == 1) {
							$showRefereeEntity->setTrida(105);
							$this->save($showRefereeEntity);
						}
						if ($row['t50'] == 1) {
							$showRefereeEntity->setTrida(106);
							$this->save($showRefereeEntity);
						}
						if ($row['t60'] == 1) {
							$showRefereeEntity->setTrida(107);
							$this->save($showRefereeEntity);
						}
						if ($row['t70'] == 1) {
							$showRefereeEntity->setTrida(108);
							$this->save($showRefereeEntity);
						}
						if ($row['t80'] == 1) {
							$showRefereeEntity->setTrida(109);
							$this->save($showRefereeEntity);
						}
						if ($row['t90'] == 1) {
							$showRefereeEntity->setTrida(110);
							$this->save($showRefereeEntity);
						}
						if ($row['t100'] == 1) {
							$showRefereeEntity->setTrida(111);
							$this->save($showRefereeEntity);
						}
						if ($row['t110'] == 1) {
							$showRefereeEntity->setTrida(112);
							$this->save($showRefereeEntity);
						}
						if ($row['t120'] == 1) {
							$showRefereeEntity->setTrida(113);
							$this->save($showRefereeEntity);
						}
					}
				}
			}
			//$this->connection->query("#RENAME TABLE v2j to migrated_v2j");
			$this->connection->commit();
		} catch (\Exception $e) {
				$this->connection->rollback();
				throw $e;
		}
	}
}