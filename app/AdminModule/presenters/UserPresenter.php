<?php

namespace App\AdminModule\Presenters;

use App\AdminModule\Model;
use App\Enum\UserRoleEnum;
use App\Forms\UserForm;
use App\Model\Entity\UserEntity;
use App\Model\UserRepository;
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

	public function saveUser($form, $values) {
		$userEntity = new UserEntity();
		$userEntity->hydrate((array)$values);
		$userEntity->setPassword(Passwords::hash($userEntity->getPassword()));

		try {
			$this->userRepository->saveUser($userEntity);
			if (isset($values['id']) && $values['id'] != "") {
				$this->flashMessage(USER_EDITED, "alert-success");
			} else {
				$this->flashMessage(USER_ADDED, "alert-success");
			}
		} catch (\Exception $e) {
			$this->flashMessage(USER_EDIT_SAVE_FAILED, "alert-danger");
		}
		$this->redirect("Default");
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
			$this['editForm']->setDefaults($userEntity->extract());
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