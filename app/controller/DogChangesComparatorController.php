<?php

namespace App\Controller;

use App\Enum\DogChangeStateEnum;
use App\Forms\WebconfigForm;
use App\Model\AwaitingChangesRepository;
use App\Model\DogRepository;
use App\Model\Entity\AwaitingChangesEntity;
use App\Model\Entity\BreederEntity;
use App\Model\Entity\DogEntity;
use App\Model\Entity\DogHealthEntity;
use App\Model\EnumerationRepository;
use App\Model\UserRepository;
use App\Model\WebconfigRepository;
use Dibi\DateTime;
use Nette\Application\UI\Presenter;
use Nette\Security\User;

class DogChangesComparatorController {

	const TBL_DOG_NAME = "appdata_pes";
	const TBL_DOG_HEALTH_NAME = "appdata_zdravi";

	/** @var AwaitingChangesRepository */
	private $awaitingChangeRepository;

	/** @var User */
	private $user;

	/** @var WebconfigRepository */
	private $webconfigRepository;

	/** @var UserRepository */
	private $userRepository;

	/** @var  DogRepository */
	private $dogRepository;

	/** @var EnumerationRepository */
	private $enumerationRepository;

	public function __construct(
		AwaitingChangesRepository $awaitingChangesRepository,
		User $user,
		WebconfigRepository $webconfigRepository,
		UserRepository $userRepository,
		DogRepository $dogRepository,
		EnumerationRepository $enumerationRepository
	) {
		$this->awaitingChangeRepository = $awaitingChangesRepository;
		$this->user = $user;
		$this->webconfigRepository = $webconfigRepository;
		$this->userRepository = $userRepository;
		$this->dogRepository = $dogRepository;
		$this->enumerationRepository = $enumerationRepository;
	}

	/**
	 * @param DogHealthEntity[] $currentDogHealth
	 * @param DogHealthEntity[] $newDogHealth
	 */
	public function compareSaveDogHealth(array $currentDogHealth, array $newDogHealth) {
		$changes = [];
		$currentDogHealth = $this->arrayWithTypeKey($currentDogHealth);
		foreach ($newDogHealth as $requiredHealth) {    // projíždím co vyplnili uživatel
			$changeMaster = new AwaitingChangesEntity();
			$changeMaster->setPID($requiredHealth->getPID());
			$changeMaster->setUID($this->user->getId());
			$changeMaster->setStav(DogChangeStateEnum::INSERTED);
			$changeMaster->setDatimVlozeno(new DateTime());
			$changeMaster->setTabulka(self::TBL_DOG_HEALTH_NAME);
			$changeMaster->setCID($requiredHealth->getTyp());
			if ($this->isSomethingFilled($requiredHealth)) {    // a zkontroluji zda něco vyplnil
				if (isset($currentDogHealth[$requiredHealth->getTyp()])) {    // jde o editovaný záznam
					$curDogHealth = $currentDogHealth[$requiredHealth->getTyp()];
					$changeMaster->setZID($curDogHealth->getID());                    // takže známe jeho ID
					if ($requiredHealth->getVysledek() != $curDogHealth->getVysledek()) {
						$change = clone($changeMaster);
						$change->setSloupec("Vysledek");
						$change->setAktualniHodnota($curDogHealth->getVysledek());
						$change->setPozadovanaHodnota($requiredHealth->getVysledek());
						$changes[] = $change;
					}
					if ($requiredHealth->getKomentar() != $curDogHealth->getKomentar()) {
						$change = clone($changeMaster);
						$change->setSloupec("Komentar");
						$change->setAktualniHodnota($curDogHealth->getKomentar());
						$change->setPozadovanaHodnota($requiredHealth->getKomentar());
						$changes[] = $change;
					}
					if (
						($requiredHealth->getDatum() != null) && ($curDogHealth->getDatum() != null)
						&& ($requiredHealth->getDatum()->format(DogHealthEntity::MASKA_DATA) !== $curDogHealth->getDatum()->format(DogHealthEntity::MASKA_DATA))
					) {
						$change = clone($changeMaster);
						$change->setSloupec("Datum");
						$change->setAktualniHodnota($curDogHealth->getDatum());
						$change->setPozadovanaHodnota($requiredHealth->getDatum());
						$changes[] = $change;
					}
					if (($requiredHealth->getVeterinar() != 0) && ($requiredHealth->getVeterinar() != $curDogHealth->getVeterinar())) {
						$change = clone($changeMaster);
						$change->setSloupec("Veterinar");
						$change->setAktualniHodnota($curDogHealth->getVeterinar());
						$change->setPozadovanaHodnota($requiredHealth->getVeterinar());
						$changes[] = $change;
					}
				} else {    // jde o nový záznam (tohle je zavádějicí,protože vždy když dělám insert nového psa tak vložím všechny typy zdraví akorát prázdný)
					if ($requiredHealth->getVysledek() != "") {
						$change = clone($changeMaster);
						$change->setSloupec("Vysledek");
						$change->setPozadovanaHodnota($requiredHealth->getVysledek());
						$changes[] = $change;
					}
					if ($requiredHealth->getKomentar() != "") {
						$change = clone($changeMaster);
						$change->setSloupec("Komentar");
						$change->setPozadovanaHodnota($requiredHealth->getKomentar());
						$changes[] = $change;
					}
					if ($requiredHealth->getDatum() !== null) {
						$change = clone($changeMaster);
						$change->setSloupec("Datum");
						$change->setPozadovanaHodnota($requiredHealth->getDatum());
						$changes[] = $change;
					}
					if ($requiredHealth->getVeterinar() != 0) {
						$change = clone($changeMaster);
						$change->setSloupec("Veterinar");
						$change->setPozadovanaHodnota($requiredHealth->getVeterinar());
						$changes[] = $change;
					}
				}
			}
		}
		$this->awaitingChangeRepository->writeChanges($changes);	// zapíšu změny
	}

	/**
	 * Vrátí pole aktuálního zdraví kde je v klíči pole jeho typ
	 * @param DogHealthEntity[] $currentDogHealths
	 */
	private function arrayWithTypeKey(array $currentDogHealths) {
		$array = [];
		foreach ($currentDogHealths as $health) {
			$array[$health->getTyp()] = $health;
		}

		return $array;
	}

	/**
	 * @param DogHealthEntity $dogHealthEntity
	 * @return bool
	 */
	private function isSomethingFilled(DogHealthEntity $dogHealthEntity) {
		if (
			(trim($dogHealthEntity->getVeterinar()) != "")
			|| (trim($dogHealthEntity->getKomentar()) != "")
			|| ($dogHealthEntity->getDatum() != null)
			|| (trim($dogHealthEntity->getVysledek()) != "")
		) {
			return true;
		}

		return false;
	}

	/**
	 * @param DogEntity $currentDog
	 * @param DogEntity $newDog
	 * @param string [$dogUrl]
	 */
	public function compareSaveDog(DogEntity $currentDog, DogEntity $newDog,$dogUrl = "") {
		$enumValues = [
			// název sloupce v DB psa => číslo číselníků
			'Pohlavi' => EnumerationRepository::POHLAVI,
			'Plemeno' => EnumerationRepository::PLEMENO,
			'Barva' => EnumerationRepository::BARVA,
			'Srst' => EnumerationRepository::SRST,
			'Varlata' => EnumerationRepository::VARLATA,
			'Skus' => EnumerationRepository::SKUS,
			'Chovnost' => EnumerationRepository::CHOVNOST
		];
		$changes = [];
		foreach ($currentDog as $property => $currentValue) {
			$newValue = $newDog->{$property};
			if ($currentValue != $newValue) {
				$awaitingEntity = new AwaitingChangesEntity();
				$awaitingEntity->setAktualniHodnota($currentValue);
				$awaitingEntity->setPozadovanaHodnota($newValue);
				$awaitingEntity->setPID($currentDog->getID());
				$awaitingEntity->setDatimVlozeno(new DateTime());
				$awaitingEntity->setTabulka(self::TBL_DOG_NAME);
				$awaitingEntity->setSloupec($property);
				$awaitingEntity->setUID($this->user->getId());
				$awaitingEntity->setStav(DogChangeStateEnum::INSERTED);

				if (array_key_exists($property, $enumValues)) {
					$awaitingEntity->setCID($enumValues[$property]);
				}

				$changes[] = $awaitingEntity;
			}
		}
		$this->awaitingChangeRepository->writeChanges($changes);        // zapíšu změny

		$userEntity = $this->userRepository->getUser($this->user->getId());
		$emailFrom = $this->webconfigRepository->getByKey(WebconfigRepository::KEY_CONTACT_FORM_RECIPIENT,
			WebconfigRepository::KEY_LANG_FOR_COMMON);

		// email pro uživatele
		$body = sprintf(AWAITING_EMAIL_USER_DOG_BODY, $dogUrl);
		//EmailController::SendPlainEmail($emailFrom, $userEntity->getEmail(), AWAITING_EMAIL_USER_DOG_SUBJECT, $body);		// TODO
		// email pro admina/y
		$body = sprintf(AWAITING_EMAIL_ADMIN_DOG_BODY, $dogUrl);
		EmailController::SendPlainEmail($userEntity->getEmail(), $emailFrom, AWAITING_EMAIL_ADMIN_DOG_SUBJECT,
			$body);
	}

	public function compareSaveOwners(array $currentOwners, array $newOwners) {

	}

	public function compareSaveBreeder(BreederEntity $currentBreeder, BreederEntity $newBreeder) {

	}

	public function compareNewFiles(array $files) {

	}

	// FUNKCE GENERUJICI HTML

	/**
	 * @param Presenter $presenter
	 * @param string $currentLang
	 * @return string
	 */
	public function generateAwaitingChangesHtml(Presenter $presenter, $currentLang) {
		$htmlOut = "
		<table class='table table-striped'>
			<thead>
				<tr>
					<th>" .AWAITING_CHANGES_DOG  . "</th>
					<th>". AWAITING_CHANGES_USER . "</th>
					<th>" .AWAITING_CHANGES_TIMESTAMP . "</th>
					<th>" .AWAITING_CHANGES_WHAT ."</th>
					<th>" .AWAITING_CHANGES_ORIGINAL_VALUE ."</th>
					<th>" .AWAITING_CHANGES_WANTED_VALUE ."</th>
					<th></th>
				</tr>
			</thead>
			<tbody>";

		$awaitingChanges = $this->awaitingChangeRepository->findAwaitingChanges();
		foreach ($awaitingChanges as $awaitingChange) {
			$htmlOut .= "<tr><td>";
			$dog = $this->dogRepository->getDog($awaitingChange->getpID());
			if ($dog != null) {
				$htmlOut .= "<a href='" . $presenter->link(':Frontend:FeItem1velord2:view', $dog->getID()) . "'>" . $dog->getTitulyPredJmenem() . " " . $dog->getJmeno() . " " . $dog->getTitulyZaJmenem() . "</a>";
			}
			$htmlOut .= '</td><td>';
			$user = $this->userRepository->getUser($awaitingChange->getUID());
			if ($user != null) {
				$htmlOut .= $user->getTitleBefore() . " " . $user->getName() . " " . $user->getSurname() . " " . $user->getTitleAfter();
			}
			$htmlOut .= '</td><td >';

			if ($awaitingChange->getDatimVlozeno() != null) {
				$htmlOut .= $awaitingChange->getDatimVlozeno()->format('d.m.Y H:i:s');
			}
			$htmlOut .= '</td><td>' . $awaitingChange->getSloupec() . '</td><td>';

			if ($awaitingChange->getTabulka() == self::TBL_DOG_HEALTH_NAME) {	// tabulku zdravi osetrime jinak

			} else {
				if ($awaitingChange->getCID() == null) {    // není číselník
					$htmlOut .= $awaitingChange->getAktualniHodnota();
				} else {    // je číselník
					$htmlOut .= $this->enumerationRepository->findEnumItemByOrder($currentLang,$awaitingChange->getAktualniHodnota());
				}
			}
			$htmlOut .= '</td><td>';

			if ($awaitingChange->getTabulka() == self::TBL_DOG_HEALTH_NAME) {    // tabulku zdravi osetrime jinak
			} else {
				if ($awaitingChange->getCID() == null) {    // není číselník
					$htmlOut .= $awaitingChange->getPozadovanaHodnota();
				} else {        // je číselník
					$htmlOut .= $this->enumerationRepository->findEnumItemByOrder($currentLang, $awaitingChange->getPozadovanaHodnota());
				}
			}
			$htmlOut .= '</td><td class="alignRight">';
			$htmlOut .= "<a href='" . $presenter->link("proceedChange", $awaitingChange->getID()) . "'>";
			$htmlOut .= '<span class="glyphicon glyphicon-ok colorGreen"></span></a>&nbsp;&nbsp;';
			$htmlOut .= "<a href='#' data-href='" . $presenter->link('declineChange', $awaitingChange->getID()) . "' class='colorRed' data-toggle='modal' data-target='#confirm-delete' title='" . AWAITING_CHANGES_DECLINE . "'><span class='glyphicon glyphicon-remove colorRed'></span ></a>";
			$htmlOut .= '</td></tr>';
		}
		$htmlOut .=	'</tbody></table>';

		return $htmlOut;
	}
}