<?php

namespace App\Controller;

use App\Enum\DogChangeStateEnum;
use App\Forms\WebconfigForm;
use App\Model\AwaitingChangesRepository;
use App\Model\Entity\AwaitingChangesEntity;
use App\Model\Entity\BreederEntity;
use App\Model\Entity\DogEntity;
use App\Model\Entity\DogHealthEntity;
use App\Model\EnumerationRepository;
use App\Model\UserRepository;
use App\Model\WebconfigRepository;
use Dibi\DateTime;
use Nette\Security\User;

class DogChangesComparatorController {

	const TBL_DOG_NAME = "appdata_pes";
	const TBL_DOG_HEALTH_NAME = "appdata_zdravi";

	/** @var AwaitingChangesRepository */
	private $awaitingChangeRepository;

	/** @var User */
	private $user;

	/** @var WebconfigRepository  */
	private $webconfigRepository;

	/** @var UserRepository */
	private $userRepository;

	public function __construct(AwaitingChangesRepository $awaitingChangesRepository, User $user, WebconfigRepository $webconfigRepository, UserRepository $userRepository) {
		$this->awaitingChangeRepository = $awaitingChangesRepository;
		$this->user = $user;
		$this->webconfigRepository = $webconfigRepository;
		$this->userRepository = $userRepository;
	}

	/**
	 * @param DogHealthEntity[] $currentDogHealth
	 * @param DogHealthEntity[] $newDogHealth
	 */
	public function compareSaveDogHealth(array $currentDogHealth, array $newDogHealth) {
		$changes = [];
		foreach ($newDogHealth as $requiredHealth) {
			foreach ($currentDogHealth as $curDogHealth) {
				if ($requiredHealth->getTyp() == $curDogHealth->getTyp()) {
					$change = new AwaitingChangesEntity();
					$change->setPID($curDogHealth->getPID());
					$change->setUID($this->user->getId());
					$change->setZID($curDogHealth->getID());
					$change->setStav(DogChangeStateEnum::INSERTED);
					$change->setDatimVlozeno(new DateTime());
					$change->setTabulka(self::TBL_DOG_HEALTH_NAME);
					$change->setCID();	// TODO
					if ($requiredHealth->getVysledek() != $curDogHealth->getVysledek()) {
						$change->setSloupec("Vysledek");
						$change->setAktualniHodnota($curDogHealth->getVysledek());
						$change->setPozadovanaHodnota($requiredHealth->getVysledek());
						$changes[] = $change;
					}
					if ($requiredHealth->getKomentar() != $curDogHealth->getKomentar()) {
						$change->setSloupec("Komentar");
						$change->setAktualniHodnota($curDogHealth->getKomentar());
						$change->setPozadovanaHodnota($requiredHealth->getKomentar());
						$changes[] = $change;
					}
					if ($requiredHealth->getDatum() !== $curDogHealth->getDatum()) {
						$change->setSloupec("Datum");
						$change->setAktualniHodnota($curDogHealth->getDatum());
						$change->setPozadovanaHodnota($requiredHealth->getDatum());
						$changes[] = $change;
					}
					if ($requiredHealth->getVeterinar() != $curDogHealth->getVeterinar()) {
						$change->setSloupec("Veterinar");
						$change->setAktualniHodnota($curDogHealth->getVeterinar());
						$change->setPozadovanaHodnota($requiredHealth->getVeterinar());
						$changes[] = $change;
					}
				}
			}
		}
		dump($currentDogHealth, $newDogHealth); die;
	}

	/**
	 * @param DogEntity $currentDog
	 * @param DogEntity $newDog
	 * @param string [$dogUrl]
	 */
	public function compareSaveDog(DogEntity $currentDog, DogEntity $newDog, $dogUrl = "") {
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
		foreach($currentDog as $property => $currentValue) {
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
		$this->awaitingChangeRepository->writeDogChanges($changes);		// zapíšu změny

		$userEntity = $this->userRepository->getUser($this->user->getId());
		$emailFrom = $this->webconfigRepository->getByKey(WebconfigRepository::KEY_CONTACT_FORM_RECIPIENT, WebconfigRepository::KEY_LANG_FOR_COMMON);

		// email pro uživatele
		$body = sprintf(AWAITING_EMAIL_USER_DOG_BODY, $dogUrl);
		//EmailController::SendPlainEmail($emailFrom, $userEntity->getEmail(), AWAITING_EMAIL_USER_DOG_SUBJECT, $body);		// TODO
		// email pro admina/y
		$body = sprintf(AWAITING_EMAIL_ADMIN_DOG_BODY, $dogUrl);
		EmailController::SendPlainEmail($userEntity->getEmail(), $emailFrom, AWAITING_EMAIL_ADMIN_DOG_SUBJECT, $body);
	}

	public function compareSaveOwners(array $currentOwners, array $newOwners) {

	}

	public function compareSaveBreeder(BreederEntity $currentBreeder, BreederEntity $newBreeder) {

	}

	public function compareNewFiles(array $files) {

	}
}