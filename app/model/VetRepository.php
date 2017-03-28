<?php

namespace App\Model;

use App\Model\Entity\VetEntity;

class VetRepository extends BaseRepository {

	/**
	 * @return array
	 */
	public function FindVets() {
		$query = "select * from appdata_veterinar";
		$result = $this->connection->query($query);

		$vets = [];
		foreach ($result->fetchAll() as $row) {
			$vet = new VetEntity();
			$vet->hydrate($row->toArray());
			$users[] = $vet;
		}

		return $vets;
	}
}