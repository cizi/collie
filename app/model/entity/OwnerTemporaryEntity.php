<?php

namespace App\Model\Entity;

class OwnerTemporaryEntity {

	/** @var int */
	private $id;

	/** @var int */
	private $utID;

	/** @var int */
	private $pID;

	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @param int $id
	 */
	public function setId($id) {
		$this->id = $id;
	}

	/**
	 * @return int
	 */
	public function getUtID() {
		return $this->utID;
	}

	/**
	 * @param int $utID
	 */
	public function setUtID($utID) {
		$this->utID = $utID;
	}

	/**
	 * @return int
	 */
	public function getPID() {
		return $this->pID;
	}

	/**
	 * @param int $pID
	 */
	public function setPID($pID) {
		$this->pID = $pID;
	}

	/**
	 * @param array $data
	 */
	public function hydrate(array $data) {
		$this->id = (isset($data['id']) ? $data['id'] : null);
		$this->pID = (isset($data['pID']) ? $data['pID'] : null);
		$this->utID = (isset($data['utID']) ? $data['utID'] : null);
	}

	/**
	 * @return array
	 */
	public function extract() {
		return [
			'id' => $this->id,
			'pID' => $this->pID,
			'utID' => $this->utID
		];
	}
}