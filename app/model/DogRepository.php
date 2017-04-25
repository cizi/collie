<?php

namespace App\Model;

use App\Forms\DogFilterForm;
use App\Model\Entity\BreederEntity;
use App\Model\Entity\DogEntity;
use App\Model\Entity\DogFileEntity;
use App\Model\Entity\DogHealthEntity;
use App\Model\Entity\DogOwnerEntity;
use App\Model\Entity\DogPicEntity;
use Dibi\Connection;
use Nette\Application\UI\Presenter;
use Nette\Http\Session;
use Nette\Utils\DateTime;
use Nette\Utils\Paginator;

class DogRepository extends BaseRepository {

	/** @const klíč pro poslední předcůdce psa */
	const SESSION_LAST_PREDECESSOR = 'lastPredecessor';

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
			$dog = $row->toArray();
			$dogs[$dog['ID']] = $dog['TitulyPredJmenem'] . " " . $dog['Jmeno'] . " " . $dog['TitulyZaJmenem'];
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
			$dog = $row->toArray();
			$dogs[$dog['ID']] = $dog['TitulyPredJmenem'] . " " . $dog['Jmeno'] . " " . $dog['TitulyZaJmenem'];
		}

		return $dogs;
	}

	/**
	 * @return DogEntity[]
	 */
	public function findDogs(Paginator $paginator, array $filter, $owner = null) {
		if (empty($filter) && ($owner == null)) {
			$query = ["select * from appdata_pes limit %i , %i", $paginator->getOffset(), $paginator->getLength()];
		} else {
			$query[] = "select *, ap.ID as ID from appdata_pes as ap ";
			foreach ($this->getJoinsToArray($filter, $owner) as $join) {
				$query[] = $join;
			}
			$query[] = "where 1 and ";
			$query[] = $this->getWhereFromKeyValueArray($filter, $owner);
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
	public function getDogsCount(array $filter, $owner = null) {
		if (empty($filter) && ($owner == null)) {
			$query = "select count(ID) as pocet from appdata_pes";
		} else {
			$query[] = "select count(distinct ap.ID) as pocet from appdata_pes as ap ";
			foreach ($this->getJoinsToArray($filter, $owner) as $join) {
				$query[] = $join;
			}
			$query[] = "where 1 and ";
			$query[] = $this->getWhereFromKeyValueArray($filter, $owner);
		}
		$row = $this->connection->query($query);

		return ($row ? $row->fetch()['pocet'] : 0);
	}

	/**
	 * Připraví joiny tabulek
	 * @param array $filter
	 * @return array
	 */
	private function getJoinsToArray($filter, $owner = null) {
		$joins = [];
		if ($owner != null) {
			$joins[] = "left join `appdata_majitel` as am on ap.ID = am.pID";
		}
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
	private function getWhereFromKeyValueArray(array $filter, $owner = null) {
		$dbDriver = $this->connection->getDriver();
		$return = "";
		$currentLang = $this->langRepository->getCurrentLang($this->session);
		if ($owner != null) {
			$return .= sprintf("am.uID = %d", $owner);
			$return .= (count($filter) > 0 ? " and " : "");
		}

		if (isset($filter[DogFilterForm::DOG_FILTER_LAND])) {
			$return .= sprintf("u.state = %s", $dbDriver->escapeText($filter[DogFilterForm::DOG_FILTER_LAND]));
			$return .= (count($filter) > 1 ? " and " : "");
			unset($filter[DogFilterForm::DOG_FILTER_LAND]);
		}
		if (isset($filter[DogFilterForm::DOG_FILTER_BREEDER])) {
			$return .= sprintf("ac.uID = %d", $dbDriver->escapeText($filter[DogFilterForm::DOG_FILTER_BREEDER]));
			$return .= (count($filter) > 1 ? " and " : "");
			unset($filter[DogFilterForm::DOG_FILTER_BREEDER]);
		}
		if (isset($filter["Jmeno"])) {
			$return .= 	sprintf("(CONCAT_WS(' ', TitulyPredJmenem, Jmeno, TitulyZaJmenem) like %s)", $dbDriver->escapeLike($filter["Jmeno"], 0));
			unset($filter["Jmeno"]);
		}
		if (isset($filter[DogFilterForm::DOG_FILTER_HEALTH])) {
			$return .= sprintf("az.Typ = %d", $dbDriver->escapeText($filter[DogFilterForm::DOG_FILTER_HEALTH]));
			$return .= (count($filter) > 1 ? " and " : "");
			unset($filter[DogFilterForm::DOG_FILTER_HEALTH]);
		}

		if (isset($filter[DogFilterForm::DOG_FILTER_PROB_DKK]) || isset($filter[DogFilterForm::DOG_FILTER_PROB_DLK])) {
			$dkk = $this->enumRepository->findEnumItemByOrder($currentLang, $filter[DogFilterForm::DOG_FILTER_PROB_DKK]);
			$dlk = $this->enumRepository->findEnumItemByOrder($currentLang, $filter[DogFilterForm::DOG_FILTER_PROB_DLK]);
			if ($dkk != "" && $dlk != "") {
				$return .= sprintf("(az.Typ in (65,66) and az.Vysledek in (%s, %s))", $dbDriver->escapeText($dkk), $dbDriver->escapeText($dlk));
				//$return .= "((az.Typ = 65 and az.Vysledek = '"  . $dkk . "') and (az.Typ = 66 and az.Vysledek = '"  . $dlk . "'))";
			} else if ($dkk != "") {
				$return .= sprintf("(az.Typ = 65 and az.Vysledek = %s)", $dbDriver->escapeText($dkk));
			} else if ($dlk != "") {
				$return .= sprintf("(az.Typ = 66 and az.Vysledek = %s)", $dbDriver->escapeText($dlk));
			}
			unset($filter[DogFilterForm::DOG_FILTER_PROB_DKK]);
			unset($filter[DogFilterForm::DOG_FILTER_PROB_DLK]);
			$return .= (count($filter) > 0 ? " and " : "");
		}

		$i = 0;
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
	 * @param int $pID
	 */
	public function findSiblings($pID) {
		$siblings = [];
		$dog= $this->getDog($pID);
		if (($dog != null) && ($dog->getMID() != null) && ($dog->getOID() != null)) {
			$query = ["select * from appdata_pes where mID = %i and oID = %i and ID <> %i", $dog->getMID(), $dog->getOID(), $dog->getID()];
			$result = $this->connection->query($query);

			foreach ($result->fetchAll() as $row) {
				$sibling = new DogEntity();
				$sibling->hydrate($row->toArray());
				$siblings[] = $sibling;
			}
		}

		return $siblings;
	}

	/**
	 * @param int $pID
	 * @return DogEntity[]
	 */
	public function findDescendants($pID) {
		$descendants = [];
		$dog = $this->getDog($pID);
		if ($dog != null) {
			if ($dog->getPohlavi() == self::MALE_ORDER) {
				$query = ["select * from appdata_pes where oID = %i", $dog->getID()];
			} else {
				$query = ["select * from appdata_pes where mID = %i", $dog->getID()];
			}
			$result = $this->connection->query($query);
			foreach ($result->fetchAll() as $row) {
				$descendant = new DogEntity();
				$descendant->hydrate($row->toArray());
				$descendants[] = $descendant;
			}
		}

		return $descendants;
	}

	/**
	 * @param int $id
	 * @return DogHealthEntity[]
	 */
	public function findHealthsByDogId($id) {
		$query = ["select * from appdata_zdravi where pID = %i and Vysledek <> ''", $id];
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

				$query = ["delete from appdata_pes_soubory where pID = %i", $id];    // pak smažu ostaní soubory
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
	 * @param DogFileEntity[]
	 */
	public function save(DogEntity $dogEntity, array $dogPics, array $dogHealth, array $breeders, array $owners, array $dogFiles) {
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

			/** @var DogFileEntity $dogFile */
			foreach ($dogFiles as $dogFile) {
				$dogFile->setPID($dogEntity->getID());
				$this->saveDogFile($dogFile);
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
	 * @param DogPicEntity $dogFileEntity
	 */
	public function saveDogFile(DogFileEntity $dogFileEntity) {
		$picQuery = ["insert into appdata_pes_soubory ", $dogFileEntity->extract()];
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
	 * @param int $pID
	 * @return DogFileEntity[]
	 */
	public function findDogFiles($pID) {
		$query = ["select * from appdata_pes_soubory where pID = %i order by id, typ desc", $pID];
		$result = $this->connection->query($query);

		$files = [];
		foreach ($result->fetchAll() as $row) {
			$dogFile = new DogFileEntity();
			$dogFile->hydrate($row->toArray());
			$files[] = $dogFile;
		}

		return $files;
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
	 * @return bool
	 */
	public function deleteDogFile($id) {
		$return = true;
		if (!empty($id)) {
			$query = ["delete from appdata_pes_soubory where id = %i", $id];
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

	// ------ příbuzbost ----
	/**
	 * @param int $pID
	 * @param int $fID
	 * @return float
	 */
	public function genealogRelationship($pID,$fID) {
		$coef = floor($this->genealogRshipGo($pID,$fID,1)*10000)/100;
		return $coef;
	}

	/**
	 * @param int $ID1
	 * @param int $ID2
	 * @param int $level
	 * @return number
	 */
	public function genealogRshipGo($ID1,$ID2,$level) {
		$deepMarkArray = [];
		$tree1 = array(array());
		$this->genealogGetRshipPedigree(NULL, $ID1, 0, 4, $tree1);
		$tree1Toc = array_shift($tree1);

		$tree2 = array(array());
		$this->genealogGetRshipPedigree(NULL, $ID2, 0, 4, $tree2);
		$tree2Toc = array_shift($tree2);

		$coef = 0;
		foreach ($tree1 as $index1 => $dog1) {
			if (in_array($dog1['ID'], $tree2Toc)) {

				//naslo se!!! Najdeme vyskyty v Tree2 a promazeme
				foreach ($tree2 as $index2 => $dog2) {
					if (($dog2['ID'] == $dog1['ID']) and ($dog1['dID'] != $dog2['dID'])) {
						if (!in_array($dog1['ID'], $deepMarkArray)) {
							$deepMarkArray[] = $dog1['ID'];
						}
						$subcoef = pow(0.5, $dog1['level'] + $dog2['level'] + 1);
						$coef += $subcoef;
					}
				}
			}
		}

		return $coef;
	}

	/**
	 * Funkce pro zjisteni pribuznosti
	 *
	 * @param $dID
	 * @param $ID
	 * @param $level
	 * @param $levels
	 * @param $output
	 * @param array $route
	 */
	private function genealogGetRshipPedigree($dID,$ID,$level,$levels,&$output,$route = array()) {
		if (($level > $levels)) {
			return;
		}
		if (($ID == NULL)) {
			$GLOBALS['lastRship'] = false;
			return;
		}
		$query = ["select pes.ID AS ID, pes.Jmeno AS Jmeno, pes.oID AS oID, pes.mID AS mID FROM appdata_pes as pes WHERE ID= %i LIMIT 1", $ID];
		$row = $this->connection->query($query)->fetch()->toArray();
		$output[0][] = $ID;
		$output[] = array(
			'ID' => $ID,
			'Jmeno' => $row['Jmeno'],
			'dID' => $dID,
			'oID' => $row['oID'],
			'mID' => $row['mID'],
			'level' => $level,
			'route' => $route
		);

		$route[] = $ID;
		//if (isset($row['oID'])) {
			$this->genealogGetRshipPedigree($ID,$row['oID'],$level+1,$levels,$output,$route);
		//}
		//if (isset($row['mID'])) {
			$this->genealogGetRshipPedigree($ID,$row['mID'],$level+1,$levels,$output,$route);
		//}
	}

	/**
	 * @param int $ID
	 * @param int $max
	 * @param string $lang
	 * @param Presenter $presenter
	 * @return string
	 */
	public function genealogDeepPedigree($ID, $max, $lang, Presenter $presenter) {
		global $pedigree,$tmpColors;
		$query = ["SELECT pes.ID AS ID, pes.Jmeno AS Jmeno, pes.oID AS oID, pes.mID AS mID FROM appdata_pes as pes
										WHERE pes.ID= %i LIMIT 1", $ID];
		$row = $this->connection->query($query)->fetch()->toArray();
		$pedigree = array();
		$this->genealogDPTrace($row['oID'],1,$max, $lang);
		$this->genealogDPTrace($row['mID'],1,$max, $lang);

		return $this->genealogShowDeepPTable($max, $presenter);
	}

	/**
	 * @param int $ID
	 * @param int $level
	 * @param int $max
	 * @param string $lang
	 */
	private function genealogDPTrace($ID,$level,$max, $lang) {
		global $pedigree;
		if ($level > $max) {
			return;
		};
		if ($ID != NULL) { // predek existuje
			$query = ["SELECT pes.ID AS ID, pes.Jmeno AS Jmeno, pes.oID AS oID, pes.mID AS mID, pes.Vyska AS Vyska, pes.Pohlavi As Pohlavi,
										pes.Plemeno As Plemeno,
										pes.Barva As BarvaOrder,
										plemeno.item as Varieta,
										pes.TitulyPredJmenem AS TitulyPredJmenem,
										pes.TitulyZaJmenem AS TitulyZaJmenem,
										barva.item AS Barva
										FROM appdata_pes as pes
										LEFT JOIN enum_item as plemeno
											ON (pes.Plemeno = plemeno.order && plemeno.enum_header_id = 7 && plemeno.lang = %s)
										LEFT JOIN enum_item as barva
											ON (pes.Barva = barva.order && barva.enum_header_id = 4 && barva.lang = %s)
										WHERE pes.ID = %i
										LIMIT 1", $lang, $lang, $ID];
			$row = $this->connection->query($query)->fetch()->toArray();
			
			$query = ["SELECT item as Nazev, Vysledek FROM appdata_zdravi as zdravi LEFT JOIN enum_item as ciselnik
				ON (ciselnik.enum_header_id = 14 AND ciselnik.order = zdravi.Typ) WHERE pID = %i ORDER BY Datum DESC", $row['ID']];
			$zdravi = $this->connection->query($query)->fetch();
			$zdravi = $zdravi === false ? '' : $zdravi->toArray();

			$query = ["SELECT Vysledek AS DKK FROM appdata_zdravi WHERE pID = %i && Typ=65 ORDER BY Datum DESC LIMIT 1", $row['ID']];
			$DKK = $this->connection->query($query)->fetch();
			$DKK = $DKK === false ? '' : $DKK->toArray()['DKK'];

			$query = ["SELECT Vysledek AS DLK FROM appdata_zdravi WHERE pID = %i && Typ=66 ORDER BY Datum DESC LIMIT 1", $row['ID']];
			$DLK = $this->connection->query($query)->fetch();
			$DLK = $DLK === false ? '' : $DLK->toArray()['DLK'];

			$pedigree[] = array(
				'Uroven' => $level,
				'ID' => $row['ID'],
				'Jmeno' => $this->arGet($row,'Jmeno'),
				'Barva' => $this->arGet($row,'Barva'),
				'Varieta' => $this->arGet($row,'Varieta'),
				'TitulyPredJmenem' => $this->arGet($row,'TitulyPredJmenem'),
				'TitulyZaJmenem' => $this->arGet($row,'TitulyZaJmenem'),
				'mID' => $this->arGet($row,'mID'),
				'oID' => $this->arGet($row,'oID'),
				'Pohlavi' => $this->arGet($row,'Pohlavi'),
				'Plemeno' => $this->arGet($row,'Plemeno'),
				'BarvaOrder' => $this->arGet($row,'BarvaOrder'),
				'DKK' => $DKK,
				'DLK' => $DLK,
				'Vyska' => $this->arGet($row,'Vyska'),
				'zdravi' => $zdravi
			);
			$this->setLastPredecessorSession($level . 'mID', $this->arGet($row,'mID'));
			$this->setLastPredecessorSession($level . 'oID', $this->arGet($row,'oID'));
			$this->setLastPredecessorSession($level . 'Pohlavi', $this->arGet($row,'Pohlavi'));
			$this->setLastPredecessorSession($level . 'Plemeno', $this->arGet($row,'Plemeno'));
			$this->setLastPredecessorSession($level . 'BarvaOrder', $this->arGet($row,'BarvaOrder'));

			$this->genealogDPTrace($row['oID'], $level + 1, $max, $lang);
			$this->genealogDPTrace($row['mID'], $level + 1, $max, $lang);
		} else { // predek neexistuje
			$pedigree[] = array(
				'Uroven' => $level,
				'ID' => 0,
				'Jmeno' => '&nbsp;',
				'Barva' => '&nbsp;',
				'mID' => $this->getLastPredecessorSession(($level - 2) . 'mID'),
				'oID' => $this->getLastPredecessorSession(($level - 2) . 'oID'),
				'Pohlavi' => $this->getLastPredecessorSession(($level - 1) . 'Pohlavi'),
				'Plemeno' => $this->getLastPredecessorSession(($level - 2) . 'Plemeno'),
				'BarvaOrder' => $this->getLastPredecessorSession(($level - 2) . 'BarvaOrder')
			);
			$this->genealogDPTrace(NULL, $level + 1, $max, $lang);
			$this->genealogDPTrace(NULL, $level + 1, $max, $lang);
		}
	}

	/**
	 * @param array $array
	 * @param string $name
	 * @return string
	 */
	private function arGet($array,$name) {
		if (isset($array[$name]) and trim($array[$name]) != '') {
			return ($array[$name]);
		} else {
			return('');
		}
	}

	/**
	 * @param int $max
	 * @param Presenter $presenter
	 * @return string
	 */
	private function genealogShowDeepPTable($max, Presenter $presenter) {
		global $pedigree;
		global $deepMarkArray;
		global $deepMark;
		$maxLevel = $max;
		$htmlOutput = "<table border='0' cellspacing='1' cellpadding='3' class='genTable'><tr>";
		$lastLevel = 0;
		for ($i = 0; $i < count($pedigree); $i++) {
			if ($pedigree[$i]['Uroven'] <= $lastLevel) {
				$htmlOutput .= '<tr>';
			}
			$lastLevel = $pedigree[$i]['Uroven'];
			$adds = array();
			if (isset($pedigree[$i]['Varieta']) && $pedigree[$i]['Varieta'] != '') {
				$adds[] = $pedigree[$i]['Varieta'];
			}
			if (isset($pedigree[$i]['Barva']) && $pedigree[$i]['Barva'] != '') {
				$adds[] = $pedigree[$i]['Barva'];
			}
			if (isset($pedigree[$i]['Vyska']) && $pedigree[$i]['Vyska'] != '0') {
				$adds[] = ($pedigree[$i]['Vyska']/10) . ' cm';
			}
			if (isset($pedigree[$i]['zdravi']) && $pedigree[$i]['zdravi'] != '') {
				foreach ($pedigree[$i]['zdravi'] as $z) {
					$adds[] = '' . $pedigree[$i]['zdravi']['Nazev'] . ': ' . $pedigree[$i]['zdravi']['Vysledek'];
				}
			}
			if (count($adds) > 0) {
				$adds = '<br />'.implode(', ',$adds);
			} else {
				$adds = '';
			}
			if (isset($pedigree[$i]['TitulyPredJmenem']) && trim($pedigree[$i]['TitulyPredJmenem']) != '') {
				$pedigree[$i]['Jmeno'] = $pedigree[$i]['TitulyPredJmenem'] . ' ' . $pedigree[$i]['Jmeno'];
			}
			if (isset($pedigree[$i]['TitulyZaJmenem']) && trim($pedigree[$i]['TitulyZaJmenem']) != '') {
				$pedigree[$i]['Jmeno'] = $pedigree[$i]['Jmeno'] . ', ' . $pedigree[$i]['TitulyZaJmenem'];
			}

			if ($pedigree[$i]['ID'] != NULL) {
				if ($deepMark and in_array($pedigree[$i]['ID'], $deepMarkArray)) {
					$htmlOutput .= '<td rowspan="'.pow(2,$maxLevel - $pedigree[$i]['Uroven'] ).'" style="background:#CDA265">'
					. '<b><a href="' . $presenter->link('view', $pedigree[$i]['ID']) . '">'.$pedigree[$i]['Jmeno'].'</a></b>'.$adds . '</td>';
				} else {
					$htmlOutput .= '<td rowspan="'.pow(2,$maxLevel - $pedigree[$i]['Uroven'] ).'">
					<b><a href="' . $presenter->link('view', $pedigree[$i]['ID']) . '">'.$pedigree[$i]['Jmeno'].'</a></b>'.$adds.'</td>';
				}
			} else {
				$htmlOutput .= '<td rowspan="'.pow(2,$maxLevel - $pedigree[$i]['Uroven'] ).'">';
				if (($pedigree[$i]['oID'] != "") || ($pedigree[$i]['mID'] != "")) {
					if ($pedigree[$i]['Pohlavi'] == self::MALE_ORDER) {
						$htmlOutput .= '<a href="' . $presenter->link("addMissingDog", $pedigree[$i]['oID'], null, $pedigree[$i]['Plemeno'], $pedigree[$i]['BarvaOrder']) . '">' . DOG_FORM_PEDIGREE_ADD_MISSING . '</a>';
					} else if ($pedigree[$i]['Pohlavi'] == self::FEMALE_ORDER) {
						$htmlOutput .= '<a href="' . $presenter->link("addMissingDog", null, $pedigree[$i]['mID'], $pedigree[$i]['Plemeno'], $pedigree[$i]['BarvaOrder']) . '">' . DOG_FORM_PEDIGREE_ADD_MISSING . '</a>';
					}
				}
				$htmlOutput .= '</td>'; // <b>' . $pedigree[$i]['Jmeno'].'</b><br/> '.$adds.'</td>';
			}
			if ($pedigree[$i]['Uroven'] == $maxLevel) {
				$htmlOutput .= '</tr>';
			}
		}
		$htmlOutput .= "</table>";

		return $htmlOutput;
	}

	/**
	 * @param string $key
	 * @return string
	 */
	private function getLastPredecessorSession($key) {
		$return = "";
		if ($this->session->hasSection(self::SESSION_LAST_PREDECESSOR)) {
			$section = $this->session->getSection(self::SESSION_LAST_PREDECESSOR);
			$return = $section->{$key};
		}

		return $return;
	}

	/**
	 * @param string $key
	 * @param string $value
	 */
	private function setLastPredecessorSession($key, $value) {
		$section = $this->session->getSection(self::SESSION_LAST_PREDECESSOR);
		$section->{$key} = $value;
	}
}