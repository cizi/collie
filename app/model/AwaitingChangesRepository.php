<?php

namespace App\Model;

use App\Model\Entity\AwaitingChangesEntity;

class AwaitingChangesRepository extends BaseRepository {

	/**
	 * @return AwaitingChangesEntity[]
	 */
	public function findAwaitingChanges() {
			$query = ["select * from appdata_zmeny order by datimVlozeno asc"];
			$result = $this->connection->query($query);

			$awaitingChanges = [];
			foreach ($result->fetchAll() as $row) {
				$change = new AwaitingChangesEntity();
				$change->hydrate($row->toArray());
				$awaitingChanges[] = $change;
			}

			return $awaitingChanges;
	}
}