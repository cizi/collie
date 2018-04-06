<?php

namespace App\Model\Entity;

class UserTemporaryEntity {

	/** @var int */
	private $id;

	/** @var string */
	private $celeJmeno;

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
	 * @return string
	 */
	public function getCeleJmeno() {
		return $this->celeJmeno;
	}

	/**
	 * @param string $celeJmeno
	 */
	public function setCeleJmeno($celeJmeno) {
		$this->celeJmeno = $celeJmeno;
	}

	/**
	 * @param array $data
	 */
	public function hydrate(array $data) {
		$this->id = (isset($data['id']) ? $data['id'] : null);
		$this->celeJmeno = (isset($data['CeleJmeno']) ? $data['CeleJmeno'] : null);
		$this->pID = (isset($data['pID']) ? $data['pID'] : null);
	}

	/**
	 * @return array
	 */
	public function extract() {
		return [
			'id' => $this->id,
			'CeleJmeno' => $this->celeJmeno
		];
	}
}