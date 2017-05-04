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
	 * @param int $vID
	 * @param int $rID
	 * @param array $refereees
	 */
	public function saveReferees($vID, $rID, array $refereees) {
		try {
			$this->connection->begin();
			$this->deleteByVIDAndRID($vID, $rID);
			/** @var ShowRefereeEntity $referee */
			foreach ($refereees as $referee) {
				$this->save($referee);
			}
			$this->connection->commit();
		} catch (\Exception $e) {
			$this->connection->rollback();
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
}