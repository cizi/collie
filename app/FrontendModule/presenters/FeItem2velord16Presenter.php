<?php

namespace App\FrontendModule\Presenters;

use App\Forms\MatingListForm;
use Nette\Forms\Form;

class FeItem2velord16Presenter extends FrontendPresenter {

	/** @var  MatingListForm */
	private $matingListForm;

	public function __construct(MatingListForm $matingListForm) {
		$this->matingListForm = $matingListForm;
	}

	public function createComponentMatingListForm() {
		$form = $this->matingListForm->create($this->langRepository->getCurrentLang($this->session));
		$form->onSubmit[] = $this->submitMatingList;

		$renderer = $form->getRenderer();
		$renderer->wrappers['controls']['container'] = NULL;
		$renderer->wrappers['pair']['container'] = 'div class=form-group';
		$renderer->wrappers['pair']['.error'] = 'has-error';
		$renderer->wrappers['control']['container'] = 'div class=col-md-6';
		$renderer->wrappers['label']['container'] = 'div class="col-md-3 control-label"';
		$renderer->wrappers['control']['description'] = 'span class=help-block';
		$renderer->wrappers['control']['errorcontainer'] = 'span class=help-block';
		$form->getElementPrototype()->class('form-horizontal');

		return $form;
	}

	public function submitMatingList(Form $form) {

	}
	
}