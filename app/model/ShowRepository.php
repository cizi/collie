<?php

namespace App\Model;

use App\Model\Entity\ShowEntity;

class ShowRepository extends BaseRepository {

	/**
	 * @return ShowEntity[]
	 */
	public function findShows() {
		$query = ["select * from appdata_vystava"];
		$result = $this->connection->query($query);

		$shows = [];
		foreach ($result->fetchAll() as $row) {
			$show = new ShowEntity();
			$show->hydrate($row->toArray());
			$shows[] = $show;
		}

		return $shows;
	}

	/**
	 * @param int $id
	 * @return \Dibi\Result|int
	 */
	public function setShowDone($id) {
		$query = ["update appdata_vystava set Hotovo = 1 where ID = %i", $id];
		return $this->connection->query($query);
	}

	/**
	 * @param int $id
	 * @return \Dibi\Result|int
	 */
	public function setShowUndone($id) {
		$query = ["update appdata_vystava set Hotovo = 0 where ID = %i", $id];
		return $this->connection->query($query);
	}

}