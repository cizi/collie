<?php

namespace App\AdminModule\Presenters;

use App\Controller\DogChangesComparatorController;
use App\Controller\EmailController;
use App\Enum\StateEnum;
use App\Enum\UserRoleEnum;
use App\Forms\UserForm;
use App\Model\DogRepository;
use App\Model\Entity\UserEntity;
use App\Model\EnumerationRepository;
use App\Model\TemporaryUserRepository;
use App\Model\UserRepository;
use App\Model\WebconfigRepository;
use Nette\Application\AbortException;
use Nette\Application\UI\Form;
use Nette\Security\Passwords;

class TempUserPresenter extends SignPresenter {

	/** @var UserForm */
	private $userForm;

	/** @var DogChangesComparatorController  */
	private $dogChangesComparatorController;

	/** @var EnumerationRepository */
	private $enumerationRepository;

	/** @var DogRepository */
	private $dogRepository;

	/** @var TemporaryUserRepository  */
	private $temporaryUserRepository;

	/** @var UserRepository */
	private $userRepository;

	/**
	 * TempUserPresenter constructor.
	 * @param UserForm $userForm
	 * @param DogChangesComparatorController $dogChangesComparatorController
	 * @param EnumerationRepository $enumerationRepository
	 * @param DogRepository $dogRepository
	 * @param TemporaryUserRepository $temporaryUserRepository
	 * @param UserRepository $userRepository
	 */
	public function __construct(
		UserForm $userForm,
		DogChangesComparatorController $dogChangesComparatorController,
		EnumerationRepository $enumerationRepository,
		DogRepository $dogRepository,
		TemporaryUserRepository $temporaryUserRepository,
		UserRepository $userRepository
	) {
		$this->userForm = $userForm;
		$this->dogChangesComparatorController = $dogChangesComparatorController;
		$this->enumerationRepository = $enumerationRepository;
		$this->dogRepository = $dogRepository;
		$this->temporaryUserRepository = $temporaryUserRepository;
		$this->userRepository = $userRepository;
	}

	/**
	 * defaultní akce presenteru načte uživatele
	 */
	public function actionDefault($id, $filter) {
		$userRoles = new UserRoleEnum();
		$this->template->users = $this-> temporaryUserRepository->findAllTemporaryUsers();
		$this->template->roles = $userRoles->translatedForSelect();
		$this->template->tempUsersAsBreeders = $this->temporaryUserRepository->findTemporaryBreeders();
		$this->template->tempUsersAsOwners = $this->temporaryUserRepository->findTemporaryOwners();
	}

	/**
	 * @param int $id
	 */
	public function actionDeleteTempUser($id) {
		if ($this->temporaryUserRepository->deleteTemporaryUser($id)) {
			$this->flashMessage(USER_DELETED, "alert-success");
		} else {
			$this->flashMessage(USER_DELETED_FAILED, "alert-danger");
		}
		$this->redirect('default');
	}

	public function createComponentEditForm() {
		$form = $this->userForm->create($this->link("TempUser:Default"), $this->langRepository->getCurrentLang($this->session));
		$form->onSuccess[] = [$this, 'saveUser'];

		return $form;
	}

	/**
	 * @param int $id
	 */
	public function actionRewriteTempUser($id) {
		$this->template->user = null;
		$tempUser = $this->temporaryUserRepository->getTemporaryUserById($id);
		if ($tempUser) {
			$this['editForm']->addHidden('temUserId', $id);
			$this['editForm']['name']->setDefaultValue($tempUser->getCeleJmeno());	// default je celé jméno
			$names = explode(" ", $tempUser->getCeleJmeno());
			if (count($names) == 2) {	// pokud budou dvě tak zkusíme rozdělit
				$this['editForm']['name']->setDefaultValue(trim($names[0]));
				$this['editForm']['surname']->setDefaultValue(trim($names[1]));
			}
		}
	}

	/**
	 * @param Form $form
	 * @param $values
	 */
	public function saveUser(Form $form, $values) {
		$userEntity = new UserEntity();
		$userEntity->hydrate((array)$values);
		$userEntity->setPassword(Passwords::hash($userEntity->getPassword()));
		$userEntity->setBreed((isset($values['breed']) && $values['breed'] != 0) ? $values['breed'] : NULL);
		$userEntity->setClub((isset($values['club']) && $values['club'] != 0) ? $values['club'] : NULL);

		try {
			if ((trim($values['passwordConfirm']) == "") || (trim($values['password']) == "")) {
				$this->flashMessage(USER_EDIT_PASSWORDS_EMPTY, "alert-danger");
				$form->addError(USER_EDIT_PASSWORDS_EMPTY);
			} elseif (trim($values['passwordConfirm']) != trim($values['password'])) {
				$this->flashMessage(USER_EDIT_PASSWORDS_DOESNT_MATCH, "alert-danger");
				$form->addError(USER_EDIT_PASSWORDS_DOESNT_MATCH);
			} elseif ($this->userRepository->getUserByEmail($values['email']) == null) {
				$this->userRepository->rewriteTempUserToRegularUser($userEntity, $values['temUserId']);

				$emailFrom = $this->webconfigRepository->getByKey(WebconfigRepository::KEY_CONTACT_FORM_RECIPIENT, WebconfigRepository::KEY_LANG_FOR_COMMON);
				$subject = USER_CREATED_MAIL_SUBJECT;
				$body = sprintf(USER_CREATED_MAIL_BODY, $this->getHttpRequest()->getUrl()->getBaseUrl(), $userEntity->getEmail(), $values['password']);
				EmailController::SendPlainEmail($emailFrom, $userEntity->getEmail(), $subject, $body);
				$this->flashMessage(USER_TEMP_REWRITTEN, "alert-success");
				$this->redirect("Default");
			} else {
				$this->flashMessage(USER_EMAIL_ALREADY_EXISTS, "alert-danger");
				$form->addError(USER_EMAIL_ALREADY_EXISTS);
			}
		} catch (\Exception $e) {
			if ($e instanceof AbortException) {
				throw $e;
			} else {
				$this->flashMessage(USER_EDIT_SAVE_FAILED, "alert-danger");
				$form->addError(USER_EDIT_SAVE_FAILED);
			}
		}
	}

	/**
	 * @param int $id
	 */
	public function actionUserReferences($id) {
		$this->template->stateEnum = new StateEnum();
		$this->template->enumRepo = $this->enumerationRepository;
		$this->template->dogRepo = $this->dogRepository;
		$this->template->currentLang = $this->langRepository->getCurrentLang($this->session);
		$this->template->user = $this->temporaryUserRepository->getTemporaryUserById($id);

		$this->template->userOwnDogs = $this->temporaryUserRepository->findRecOwnersInDogs($id);
		$this->template->userBreedDogs = $this->temporaryUserRepository->findRecBreedersInDogs($id);
	}

	/**
	 * @param int $id
	 * @param int $utID
	 */
	public function actionDeleteDogTempOwner($id, $utID) {
		if ($this->temporaryUserRepository->deleteTemporaryOwnerById($id)) {
			$this->flashMessage(MENU_SETTINGS_ITEM_DELETED, "alert-success");
		} else {
			$this->flashMessage(BLOCK_SETTINGS_ITEM_DELETED_FAILED, "alert-danger");
		}
		$this->redirect("userReferences", $utID);
	}

	/**
	 * @param int $id
	 * @param int $utID
	 */
	public function actionDeleteDogTempBreeder($id, $utID) {
		if ($this->temporaryUserRepository->deleteTemporaryBreederById($id)) {
			$this->flashMessage(MENU_SETTINGS_ITEM_DELETED, "alert-success");
		} else {
			$this->flashMessage(BLOCK_SETTINGS_ITEM_DELETED_FAILED, "alert-danger");
		}
		$this->redirect("userReferences", $utID);
	}
}