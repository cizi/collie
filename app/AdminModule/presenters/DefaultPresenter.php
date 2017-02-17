<?php

namespace App\AdminModule\Presenters;

use App\Model\LangRepository;
use App\Model\UserRepository;
use App\Forms\SignForm;
use App\FrontendModule\Presenters\BasePresenter;
use Nette\Application\UI\Form;
use App\AdminModule\Presenters;

class DefaultPresenter extends BasePresenter {

	/** @var SignForm */
	public $singInForm;

	/** @var UserRepository */
	public $userRepository;

	/**
	 * @param SignForm $signForm
	 * @param UserRepository $userRepository
	 * @param LangRepository $langRepository
	 */
	public function __construct(SignForm $signForm, UserRepository $userRepository, LangRepository $langRepository) {
		$this->singInForm = $signForm;
		$this->userRepository = $userRepository;
	}

	/**
	 * Already logged in, redirect do dashboard
	 */
	public function actionDefault() {
		if ($this->user->isLoggedIn()) {
			$this->redirect('Dashboard:Default');
		}
	}

	/**
	 * Sign-in form factory.
	 * @return Form
	 */
	public function createComponentSignInForm(){
		$form = $this->singInForm->create();

		$langs = $this->langRepository->findLanguages();
		if (count($langs) == 0) {
			$form['lang']->setAttribute("style", "display: none");
		} else {
			$form['lang']->setItems($langs);
		}

		$form->onSuccess[] = $this->formSucceeded;

		return $form;
	}

	/**
	 * @param Form $form
	 * @param $values
	 */
	public function formSucceeded(Form $form, $values) {
		if ($values->remember) {
			$this->user->setExpiration('14 days', false);
		} else {
			$this->user->setExpiration('20 minutes', true);
		}

		try {
			$credentials = ['email' => $values['login'], 'password' => $values['password']];
			$identity = $this->user->getAuthenticator()->authenticate($credentials);
			$this->user->login($identity);
			$this->userRepository->updateLostLogin($identity->getId());

			$availableLnags = $this->langRepository->findLanguages();
			if (isset($values['lang']) && isset($availableLnags[$values['lang']])) {
				$this->langRepository->switchToLanguage($this->session, $values['lang']);
			}

			$this->redirect("Dashboard:Default");

		} catch (\Nette\Security\AuthenticationException $e) {
			$form->addError(ADMIN_LOGIN_FAILED);
		}
	}

	public function actionOut(){
		$this->getUser()->logout();
		$this->flashMessage(ADMIN_LOGIN_UNLOGGED);
		$this->redirect('default');
	}
}