<?php

namespace App\Model;

use App\Forms\DogFilterForm;
use App\Model\Entity\BreederEntity;
use App\Model\Entity\DogEntity;
use App\Model\Entity\DogHealthEntity;
use App\Model\Entity\DogOwnerEntity;
use App\Model\Entity\DogPicEntity;
use Dibi\Connection;
use Nette\Http\Session;
use Nette\Utils\DateTime;
use Nette\Utils\Paginator;

class DogRepository extends BaseRepository {

	/** @const znak pro nevybraného psa v selectu  */
	const NOT_SELECTED = "-";

	/** @const pořadí pro fenu */
	const FEMALE_ORDER = 30;

	/** @const pořadí pro psa */
	const MALE_ORDER = 29;

	/** @var EnumerationRepository  */
	private $enumRepository;

	/** @var \Dibi\Connection */
	protected $connection;

	/** @var Session */
	private $session;

	/** @var LangRepository */
	private $langRepository;

	/**
	 * @param EnumerationRepository $enumerationRepository
	 * @param Connection $connection
	 * @param Session $session
	 * @param LangRepository $langRepository
	 */
	public function __construct(EnumerationRepository $enumerationRepository, Connection $connection, Session $session, LangRepository $langRepository) {
		$this->enumRepository = $enumerationRepository;
		$this->session = $session;
		$this->langRepository = $langRepository;

		parent::__construct($connection);
	}

	/**
	 * @param int $id
	 * @return DogEntity
	 */
	public function getDog($id) {
		$query = ["select * from appdata_pes where ID = %i", $id];
		$row = $this->connection->query($query)->fetch();
		if ($row) {
			$dogEntity = new DogEntity();
			$dogEntity->hydrate($row->toArray());
			return $dogEntity;
		}
	}

	/**
	 * @return array
	 */
	public function findFemaleDogsForSelect($withNotSelectedOption = true) {
		$query = ["select `ID`,`TitulyPredJmenem`,`Jmeno`,`TitulyZaJmenem` from appdata_pes where Pohlavi = %i", self::FEMALE_ORDER];
		$result = $this->connection->query($query);
		$dogs = [];

		if ($withNotSelectedOption) {
			$dogs[0] = self::NOT_SELECTED;
		}
		foreach ($result->fetchAll() as $row) {
			$dog = new DogEntity();
			$dog->hydrate($row->toArray());
			$dogs[$dog->getID()] = $dog->getTitulyPredJmenem() . " " . $dog->getJmeno() . " " . $dog->getTitulyZaJmenem();
		}

		return $dogs;
	}

	/**
	 * @return DogEntity[]
	 */
	public function findMaleDogsForSelect($withNotSelectedOption = true) {
		$query = ["select `ID`,`TitulyPredJmenem`,`Jmeno`,`TitulyZaJmenem` from appdata_pes where Pohlavi = %i", self::MALE_ORDER];
		$result = $this->connection->query($query);
		$dogs = [];

		if ($withNotSelectedOption) {
			$dogs[0] = self::NOT_SELECTED;
		}
		foreach ($result->fetchAll() as $row) {
			$dog = new DogEntity();
			$dog->hydrate($row->toArray());
			$dogs[$dog->getID()] = $dog->getTitulyPredJmenem() . " " . $dog->getJmeno() . " " . $dog->getTitulyZaJmenem();
		}

		return $dogs;
	}

	/**
	 * @return DogEntity[]
	 */
	public function findDogs(Paginator $paginator, array $filter) {
		if (empty($filter)) {
			$query = ["select * from appdata_pes limit %i , %i", $paginator->getOffset(), $paginator->getLength()];
		} else {
			$query[] = "select *, ap.ID as ID from appdata_pes as ap ";
			foreach ($this->getJoinsToArray($filter) as $join) {
				$query[] = $join;
			}
			$query[] = "where 1 and ";
			$query[] = $this->getWhereFromKeyValueArray($filter);
			$query[] = " limit %i , %i";
			$query[] = $paginator->getOffset();
			$query[] = $paginator->getLength();
		}
		$result = $this->connection->query($query);

		$dogs = [];
		foreach ($result->fetchAll() as $row) {
			$dog = new DogEntity();
			$dog->hydrate($row->toArray());
			$dogs[] = $dog;
		}

		return $dogs;
	}

	/**
	 * @param array $filter
	 * @return int
	 */
	public function getDogsCount(array $filter) {
		if (empty($filter)) {
			$query = "select count(ID) as pocet from appdata_pes";
		} else {
			$query[] = "select count(distinct ap.ID) as pocet from appdata_pes as ap ";
			foreach ($this->getJoinsToArray($filter) as $join) {
				$query[] = $join;
			}
			$query[] = "where 1 and ";
			$query[] = $this->getWhereFromKeyValueArray($filter);
		}
		$row = $this->connection->query($query);

		return ($row ? $row->fetch()['pocet'] : 0);
	}

	/**
	 * Připraví joiny tabulek
	 * @param array $filter
	 * @return array
	 */
	private function getJoinsToArray($filter) {
		$joins = [];
		if (isset($filter[DogFilterForm::DOG_FILTER_LAND]) || isset($filter[DogFilterForm::DOG_FILTER_BREEDER])) {
			$joins[] = "left join `appdata_chovatel` as ac on ap.ID = ac.pID
						left join `user` as u on ac.uID = u.ID ";
			unset($filter[DogFilterForm::DOG_FILTER_LAND]);
			unset($filter[DogFilterForm::DOG_FILTER_BREEDER]);
		}

		if (
			isset($filter[DogFilterForm::DOG_FILTER_HEALTH])
			|| isset($filter[DogFilterForm::DOG_FILTER_PROB_DKK])
			|| isset($filter[DogFilterForm::DOG_FILTER_PROB_DLK]
			)) {
			$joins[] = "left join `appdata_zdravi` as az on ap.ID = az.pID ";
			unset($filter[DogFilterForm::DOG_FILTER_HEALTH]);
			unset($filter[DogFilterForm::DOG_FILTER_PROB_DKK]);
			unset($filter[DogFilterForm::DOG_FILTER_PROB_DLK]);
		}

		return $joins;
	}

	/**
	 * @param array $filer
	 * @return string
	 */
	private function getWhereFromKeyValueArray(array $filter) {
		$return = "";
		$currentLang = $this->langRepository->getCurrentLang($this->session);
		$i = 0;
		if (isset($filter[DogFilterForm::DOG_FILTER_LAND])) {
			$return .= "u.state = '" . $filter[DogFilterForm::DOG_FILTER_LAND] . "'";
			$return .= (count($filter) > 1 ? " and " : "");
			unset($filter[DogFilterForm::DOG_FILTER_LAND]);
		}
		if (isset($filter[DogFilterForm::DOG_FILTER_BREEDER])) {
			$return .= "ac.uID = " . $filter[DogFilterForm::DOG_FILTER_BREEDER];
			$return .= (count($filter) > 1 ? " and " : "");
			unset($filter[DogFilterForm::DOG_FILTER_BREEDER]);
		}
		if (isset($filter["Jmeno"])) {
			$return .= "(CONCAT_WS(' ', TitulyPredJmenem, Jmeno, TitulyZaJmenem) like '%" . $filter["Jmeno"] . "%')";
			/* $return .= "(Jmeno like '%" . $filter["Jmeno"] . "%' or ";
			$return .= "TitulyPredJmenem like '%" . $filter["Jmeno"] . "%' or ";
			$return .= "TitulyZaJmenem like '%" . $filter["Jmeno"] . "%')";
			$return .= (count($filter) > 1 ? " and " : ""); */
			unset($filter["Jmeno"]);
		}
		if (isset($filter[DogFilterForm::DOG_FILTER_HEALTH])) {
			$return .= "az.Typ = " . $filter[DogFilterForm::DOG_FILTER_HEALTH];
			$return .= (count($filter) > 1 ? " and " : "");
			unset($filter[DogFilterForm::DOG_FILTER_HEALTH]);
		}

		if (isset($filter[DogFilterForm::DOG_FILTER_PROB_DKK]) || isset($filter[DogFilterForm::DOG_FILTER_PROB_DLK])) {
			$dkk = $this->enumRepository->findEnumItemByOrder($currentLang, $filter[DogFilterForm::DOG_FILTER_PROB_DKK]);
			$dlk = $this->enumRepository->findEnumItemByOrder($currentLang, $filter[DogFilterForm::DOG_FILTER_PROB_DLK]);
			if ($dkk != "" && $dlk != "") {
				$return .= "(az.Typ in (65,66) and az.Vysledek in ('"  . $dkk . "','" . $dlk . "'))";
				//$return .= "((az.Typ = 65 and az.Vysledek = '"  . $dkk . "') and (az.Typ = 66 and az.Vysledek = '"  . $dlk . "'))";
			} else if ($dkk != "") {
				$return .= "(az.Typ = 65 and az.Vysledek = '"  . $dkk . "')";
			} else if ($dlk != "") {
				$return .= "(az.Typ = 66 and az.Vysledek = '"  . $dlk . "')";
			}
			unset($filter[DogFilterForm::DOG_FILTER_PROB_DKK]);
			unset($filter[DogFilterForm::DOG_FILTER_PROB_DLK]);
			$return .= (count($filter) > 0 ? " and " : "");
		}

		foreach ($filter as $key => $value) {
			$return .= $key . "=" . $value;
			if (($i+1) != count($filter)) {
				$return .= " and ";
			}
			$i++;
		}

		return $return;
	}

	/**
	 * @param int $id
	 * @return DogHealthEntity[]
	 */
	public function findHealthsByDogId($id) {
		$query = ["select * from appdata_zdravi where pID = %i", $id];
		$result = $this->connection->query($query);

		$dogHealths = [];
		foreach ($result->fetchAll() as $row) {
			$dogHealth = new DogHealthEntity();
			$dogHealth->hydrate($row->toArray());
			$dogHealths[] = $dogHealth;
		}

		return $dogHealths;
	}

	/**
	 * @param int $id
	 * @return bool
	 */
	public function delete($id) {
		$return = true;
		if (!empty($id)) {
			try {
				$this->connection->begin();

				$query = ["delete from appdata_pes_obrazky where pID = %i", $id];    // nejdříve smažu obrázky
				$this->connection->query($query);

				$this->deleteHealthByDogId($id);
				$this->deleteBreederByDogId($id);
				$this->deleteOwnerByDogId($id);

				$query = ["delete from appdata_pes where ID = %i", $id];    // pak smažu psa
				$this->connection->query($query);

				$this->connection->commit();
			} catch (\Exception $e) {
				$this->connection->rollback();
				$return = false;
			}
		}

		return $return;
	}

	/**
	 * @param int $pID
	 */
	private function deleteHealthByDogId($pID) {
		$query = ["delete from appdata_zdravi where pID = %i", $pID];
		$this->connection->query($query);
	}

	/**
	 * @param int $pID
	 */
	private function deleteBreederByDogId($pID) {
		$query = ["delete from appdata_chovatel where pID = %i", $pID];
		$this->connection->query($query);
	}

	/**
	 * @param int $pID
	 */
	private function deleteOwnerByDogId($pID) {
		$query = ["delete from appdata_majitel where pID = %i", $pID];
		$this->connection->query($query);
	}

	/**
	 * @param int $typ
	 * @param int $pID
	 * @return DogHealthEntity
	 */
	public function getHealthEntityByDogAndType($typ, $pID) {
		$query = ["select * from appdata_zdravi where Typ = %i and pID = %i", $typ, $pID];
		$row = $this->connection->query($query)->fetch();
		if ($row) {
			$healthEntity = new DogHealthEntity();
			$healthEntity->hydrate($row->toArray());
			return $healthEntity;
		}
	}

	/**
	 * @param DogEntity $dogEntity
	 * @param DogPicEntity[]
	 * @param DogHealthEntity[]
	 * @param BreederEntity[]
	 * @param DogOwnerEntity[]
	 */
	public function save(DogEntity $dogEntity, array $dogPics, array $dogHealth, array $breeders, array $owners) {
		try {
			$this->connection->begin();
			$dogEntity->setPosledniZmena(new DateTime());
			if ($dogEntity->getMID() == 0) {
				$dogEntity->setMID(null);
			}
			if ($dogEntity->getOID() == 0) {
				$dogEntity->setOID(null);
			}
			if ($dogEntity->getID() == null) {	// nový pes
				$query = ["insert into appdata_pes ", $dogEntity->extract()];
				$this->connection->query($query);
				$dogEntity->setID($this->connection->getInsertId());
			} else {	// editovaný pes
				$query = ["update appdata_pes set ", $dogEntity->extract(), "where ID=%i", $dogEntity->getID()];
				$this->connection->query($query);
			}
			/** @var DogHealthEntity $dogHealthEntity */
			foreach($dogHealth as $dogHealthEntity) {
				$dogHealthEntity->setPID($dogEntity->getID());
				if ($dogHealthEntity->getVeterinar() == 0) {	// pokud nebyl veterinář vybrán vynuluji jeho záznam
					$dogHealthEntity->setVeterinar(null);
				}
				if ($dogHealthEntity->getID() == null) {
					$query = ["insert into appdata_zdravi ", $dogHealthEntity->extract()];
				} else {
					$query = ["update appdata_zdravi set ", $dogHealthEntity->extract(), "where ID=%i", $dogHealthEntity->getID()];
				}
				$this->connection->query($query);
			}
			/** @var BreederEntity $breeder */
			foreach($breeders as $breeder) {
				$breeder->setPID($dogEntity->getID());
				if ($breeder->getUID() == 0) {		// pokud je v selectu vybrána nula tak mažu
					$this->deleteBreederByDogId($dogEntity->getID());
				} else {
					$query = ($breeder->getID() == null ? ["insert into appdata_chovatel ", $breeder->extract()] : ["update appdata_chovatel set ", $breeder->extract(), "where ID=%i", $breeder->getID()]);
					$this->connection->query($query);
				}
			}

			$query = ["update appdata_majitel set Soucasny = %i where pID = %i", 0, $dogEntity->getID()];	// nevím co mi nyní přijde takže všechny rovnou udělám jako bývalé majitele
			$this->connection->query($query);
			/** @var DogOwnerEntity $owner */
			foreach($owners as $owner) {
				$owner->setPID($dogEntity->getID());
				$alreadyIn = ["select * from appdata_majitel where uID = %i and pID = %i", $owner->getUID(), $dogEntity->getID()];
				$row = $this->connection->query($alreadyIn)->fetch();
				if ($row) {	// pokud existuje akorat přepnu na současného
					$dogOwn = new DogOwnerEntity();
					$dogOwn->hydrate($row->toArray());
					$query = ["update appdata_majitel set Soucasny = %i where ID = %i", $owner->isSoucasny(), $dogOwn->getID()];
				} else {	// pokud záznam neexistuje vložím jako nový současný majitel
					$query = ["insert into appdata_majitel ", $owner->extract()];
				}
				$this->connection->query($query);
			}

			/** @var DogPicEntity $dogPic */
			foreach ($dogPics as $dogPic) {
				$dogPic->setPID($dogEntity->getID());
				$dogPic->setVychozi(0);
				$this->saveDogPic($dogPic);
			}
			$this->connection->commit();
		} catch (\Exception $e) {
			$this->connection->rollback();
		}
	}

	/**
	 * @param DogPicEntity $dogPicEntity
	 */
	public function saveDogPic(DogPicEntity $dogPicEntity) {
		$picQuery = ["insert into appdata_pes_obrazky ", $dogPicEntity->extract()];
		$this->connection->query($picQuery);
	}

	/**
	 * @param int $pID
	 * @return DogPicEntity[]
	 */
	public function findDogPics($pID) {
		$query = ["select * from appdata_pes_obrazky where pID = %i", $pID];
		$result = $this->connection->query($query);

		$pics = [];
		foreach ($result->fetchAll() as $row) {
			$dogPic = new DogPicEntity();
			$dogPic->hydrate($row->toArray());
			$pics[] = $dogPic;
		}

		return $pics;
	}

	/**
	 * @param int $dogId
	 * @param int $picId
	 */
	public function setDefaultDogPic($dogId, $picId) {
		$query = ["update appdata_pes_obrazky set vychozi=0 where pID = %i", $dogId];
		$this->connection->query($query);
		$query = ["update appdata_pes_obrazky set vychozi=1 where pID = %i and id = %i", $dogId, $picId];
		$this->connection->query($query);
	}

	/**
	 * @param int $id
	 * @return bool
	 */
	public function deleteDogPic($id) {
		$return = true;
		if (!empty($id)) {
			$query = ["delete from appdata_pes_obrazky where id = %i", $id];
			$return = $this->connection->query($query) == 1 ? true : false;
		}

		return $return;
	}

	/**
	 * @param int $id
	 * @return array
	 */
	public function getDkkByDogId($id) {
		$query = ["select * from appdata_zdravi where pID = %i and Typ = %i", $id, 65];
		$result = $this->connection->query($query);

		$row = $result->fetch();
		if ($row) {
			$dogHealth = new DogHealthEntity();
			$dogHealth->hydrate($row->toArray());
			return $dogHealth;
		}
	}

	/**
	 * @param int $id
	 * @return array
	 */
	public function getDlkByDogId($id) {
		$query = ["select * from appdata_zdravi where pID = %i and Typ = %i", $id, 66];
		$result = $this->connection->query($query);

		$row = $result->fetch();
		if ($row) {
			$dogHealth = new DogHealthEntity();
			$dogHealth->hydrate($row->toArray());
			return $dogHealth;
		}
	}
}