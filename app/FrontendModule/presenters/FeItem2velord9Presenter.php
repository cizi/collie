<?php

namespace App\FrontendModule\Presenters;

use App\Forms\UserForm;
use App\Model\Entity\UserEntity;
use App\Model\UserRepository;
use Nette\Application\AbortException;
use Nette\Application\UI\Form;

class FeItem2velord9Presenter extends FrontendPresenter {

	/** @var UserForm */
	private $userForm;

	/** @var UserRepository */
	private $userRepository;

	public function startup() {
		parent::startup();
		if ($this->user->isLoggedIn() == false) {	// pokud nejsem přihlášen tak nemám co měnit -> tedy login
			$this->redirect(BasePresenter::PRESENTER_PREFIX . "1" . BasePresenter::LEVEL_ORDER_DELIMITER. "14:default");
		}
	}

	public function __construct(UserForm $userForm, UserRepository $userRepository) {
		$this->userForm = $userForm;
		$this->userRepository = $userRepository;
	}

	public function renderDefault() {
		$userEntity = $this->userRepository->getUser($this->user->getId());
		$this->template->user = $userEntity;

		if ($userEntity) {
			$this['editForm']->addHidden('id', $userEntity->getId());
			$this['editForm']['email']->setAttribute("readonly", "readonly");
			unset($this['editForm']['password']);
			unset($this['editForm']['passwordConfirm']);
			unset($this['editForm']['role']);
			unset($this['editForm']['active']);

			$this['editForm']->setDefaults($userEntity->extract());
		}
	}

	/**
	 * Vytvoří komponentu pro registraci uživatele
	 * @return Form
	 */
	public function createComponentEditForm() {
		$form = $this->userForm->create($this->link("default"), $this->langRepository->getCurrentLang($this->session));
		$form->onSubmit[] = $this->saveUser;

		/* $renderer = $form->getRenderer();
		$renderer->wrappers['controls']['container'] = NULL;
		$renderer->wrappers['pair']['container'] = 'div class=form-group';
		$renderer->wrappers['pair']['.error'] = 'has-error';
		$renderer->wrappers['control']['container'] = 'div class=col-md-6';
		$renderer->wrappers['label']['container'] = 'div class="col-md-3 control-label"';
		$renderer->wrappers['control']['description'] = 'span class=help-block';
		$renderer->wrappers['control']['errorcontainer'] = 'span class=help-block';
		$form->getElementPrototype()->class('form-horizontal'); */

		return $form;
	}

	/**
	 * Zedituje uživatele
	 * @param Form $form
	 */
	public function saveUser(Form $form) {
		try {
			$values = $form->getHttpData();
			$userEntityCurrent = $this->userRepository->getUser($this->user->getId());

			$userEntityNew = new UserEntity();
			$userEntityNew->hydrate((array)$values);
			$userEntityNew->setId($userEntityCurrent->getId());
			$userEntityNew->setEmail($userEntityCurrent->getEmail());
			$userEntityNew->setRole($userEntityCurrent->getRole());
			$userEntityNew->setActive(true);
			$userEntityNew->setNews(isset($values['news']));
			$userEntityNew->setPassword($userEntityCurrent->getPassword());
			$userEntityNew->setClub($userEntityCurrent->getClub());
			$userEntityNew->setClubNo($userEntityCurrent->getClubNo());

			if ($userEntityNew->getBreed() == 0) {
				$userEntityNew->setBreed(null);
			}

			if ($userEntityNew->getClub() == 0) {
				$userEntityNew->setClub(null);
			}

			$this->userRepository->saveUser($userEntityNew);

			if (isset($values['id']) && $values['id'] != "") {
				$this->flashMessage(USER_EDITED, "alert-success");
			} else {
				$this->flashMessage(USER_ADDED, "alert-success");
			}
		} catch (\Exception $e) {
			// dump($e->getMessage(), $values); die;
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
}