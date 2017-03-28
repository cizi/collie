<?php

namespace App\FrontendModule\Presenters;

use App\Forms\DogFilterForm;
use App\Forms\DogForm;
use Nette\Forms\Form;

class FeItem2velord11Presenter extends FrontendPresenter {

	/** @var DogFilterForm */
	private $dogFilterForm;

	public function __construct(DogFilterForm $dogFilterForm) {
		$this->dogFilterForm = $dogFilterForm;
	}

	public function actionDefault($id) {
		$this->template->dogs = [];
	}

	/**
	 * Vytvoøí komponentu pro zmìnu hesla uživatele
	 */
	public function createComponentDogFilterForm() {
		$form = $this->dogFilterForm->create($this->langRepository->getCurrentLang($this->session));
		$form->onSubmit[] = $this->saveDog;

		$renderer = $form->getRenderer();
		$renderer->wrappers['controls']['container'] = NULL;
		$renderer->wrappers['pair']['container'] = 'div class=form-group';
		$renderer->wrappers['pair']['.error'] = 'has-error';
		$renderer->wrappers['control']['container'] = 'div class=col-md-3';
		$renderer->wrappers['label']['container'] = 'div class="col-md-3 control-label"';
		$renderer->wrappers['control']['description'] = 'span class=help-block';
		$renderer->wrappers['control']['errorcontainer'] = 'span class=help-block';
		//$form->getElementPrototype()->class('form-horizontal');

		return $form;
	}

	public function actionEdit() {

	}

	public function saveDog(Form $form){

	}
}