<?php

namespace App\AdminModule\Presenters;

use App\AdminModule\Model;
use App\Enum\UserRoleEnum;
use App\Forms\UserForm;
use App\Model\Entity\UserEntity;
use App\Model\UserRepository;
use Nette\Application\AbortException;
use Nette\Application\UI\Form;
use Nette\Security\Passwords;
use Nette\Security\User;

class UserPresenter extends SignPresenter {

	/** @var UserRepository */
	private $userRepository;

	/** @var UserForm */
	private $userForm;

	/**
	 * @param UserRepository $userRepository
	 * @param UserForm $userForm
	 */
	public function __construct(UserRepository $userRepository, UserForm $userForm) {
		$this->userRepository = $userRepository;
		$this->userForm = $userForm;
	}

	/**
	 * defaultní akce presenteru naète uivatele
	 */
	public function actionDefault() {
		$userRoles = new UserRoleEnum();
		$this->template->users = $this->userRepository->findUsers();
		$this->template->roles = $userRoles->translatedForSelect();
	}

	/**
	 * @param int $id
	 */
	public function actionDeleteUser($id) {
		if ($this->userRepository->deleteUser($id)) {
			$this->flashMessage(USER_DELETED, "alert-success");
		} else {
			$this->flashMessage(USER_DELETED_FAILED, "alert-danger");
		}
		$this->redirect('default');
	}

	public function createComponentEditForm() {
		$form = $this->userForm->create($this->link("User:Default"));
		$form->onSuccess[] = $this->saveUser;

		return $form;
	}

	/**
	 * @param Form $form
	 * @param $values
	 */
	public function saveUser(Form $form, $values) {
		$userEntity = new UserEntity();
		$userEntity->hydrate((array)$values);
		$userEntity->setPassword(Passwords::hash($userEntity->getPassword()));
		$isEditation = (isset($values['id']) && $values['id'] != "");

		try {
			if ($isEditation) {	// pokud edituji tal propíšu jen heslo
				$userCurrent = $this->userRepository->getUser($this->user->getId());
				$userEntity->setPassword($userCurrent->getPassword());
				$this->userRepository->saveUser($userEntity);
				$this->flashMessage(USER_EDITED, "alert-success");
			} else {
				if ((trim($values['passwordConfirm']) == "") || (trim($values['password']) == "")) {
					$this->flashMessage(USER_EDIT_PASSWORDS_EMPTY, "alert-danger");
					$form->addError(USER_EDIT_PASSWORDS_EMPTY);
				} elseif (trim($values['passwordConfirm']) != trim($values['password'])) {
					$this->flashMessage(USER_EDIT_PASSWORDS_DOESNT_MATCH, "alert-danger");
					$form->addError(USER_EDIT_PASSWORDS_DOESNT_MATCH);
				} elseif ($this->userRepository->getUserByEmail($values['email']) == null) {
					$this->userRepository->saveUser($userEntity);
					$this->flashMessage(USER_ADDED, "alert-success");
					$this->redirect("Default");
				} else {
					$this->flashMessage(USER_EMAIL_ALREADY_EXISTS, "alert-danger");
					$form->addError(USER_EMAIL_ALREADY_EXISTS);
				}
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
	public function actionEdit($id) {
		$this->template->user = null;
		$userEntity = $this->userRepository->getUser($id);
		$this->template->user = $userEntity;

		if ($userEntity) {
			$this['editForm']->addHidden('id', $userEntity->getId());
			$this['editForm']['email']->setAttribute("readonly", "readonly");

			$this['editForm']['password']->setAttribute("readonly", "readonly");	// pokud edituji tak heslo nemìním
			$this['editForm']['passwordConfirm']->setAttribute("readonly", "readonly"); // pokud edituji tak heslo nemìním

			$this['editForm']->setDefaults($userEntity->extract());

			$this['editForm']['passwordConfirm']->setAttribute("class", "form-control");
			$this['editForm']['password']->setAttribute("class", "form-control");
		}
	}

	/**
	 *
	 */
	public function handleActiveSwitch() {
		$data = $this->request->getParameters();
		$userId = $data['idUser'];
		$switchTo = (!empty($data['to']) && $data['to'] == "false" ? false : true);

		if ($switchTo) {
			$this->userRepository->setUserActive($userId);
		} else {
			$this->userRepository->setUserInactive($userId);
		}

		$this->terminate();
	}
}