<?php

namespace App\Model;

use App\Model\Entity\LitterApplicationEntity;

class LitterApplicationRepository extends BaseRepository {

	/**
	 * @param LitterApplicationEntity $litterApplicationEntity
	 */
	public function save(LitterApplicationEntity $litterApplicationEntity) {
		if ($litterApplicationEntity->getID() == null) {
			$query = ["insert into appdata_krycilist ", $litterApplicationEntity->extract()];
		} else {
			$query = ["update appdata_krycilist set ", $litterApplicationEntity->extract(), "where ID=%i", $litterApplicationEntity->getID()];
		}
		$this->connection->query($query);
	}
}