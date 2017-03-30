<?php

namespace App\Model\Entity;

class DogPicEntity {

	/** @var int  */
	private $id;

	/** @var string  */
	private $path;

	/**
	 * @return int
	 */
	public function getDogId() {
		return $this->dogId;
	}

	/**
	 * @param int $dogId
	 */
	public function setDogId($dogId) {
		$this->dogId = $dogId;
	}

	/**
	 * @return boolean
	 */
	public function isIsMain() {
		return $this->isMain;
	}

	/**
	 * @param boolean $isMain
	 */
	public function setIsMain($isMain) {
		$this->isMain = $isMain;
	}

	/** @var int  */
	private $dogId;

	/** @var bool */
	private $isMain;

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
	public function getPath() {
		return $this->path;
	}

	/**
	 * @param string $path
	 */
	public function setPath($path) {
		$this->path = $path;
	}

	/**
	 * @param array $data
	 */
	public function hydrate($data) {
		$this->id = (isset($data['id']) ? $data['id'] : null);
		$this->path = (isset($data['path']) ? $data['path'] : null);
		$this->dogId = (isset($data['dogId']) ? $data['dogId'] : null);
		$this->isMain = (isset($data['isMain']) ? $data['isMain'] : null);
	}

	/**
	 * @return array
	 */
	public function extract() {
		return [
			'id' => $this->id,
			'path' => $this->path,
			'dogId' => $this->dogId,
			'isMain' => $this->isMain
		];
	}

}