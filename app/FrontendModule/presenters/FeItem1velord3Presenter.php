<?php

namespace App\FrontendModule\Presenters;

use App\Forms\KinshipVerificationForm;

class FeItem1velord3Presenter extends FrontendPresenter {

	/** @var  KinshipVerificationForm */
	private $kinshipVerificationForm;

	public function __construct(KinshipVerificationForm $kinshipVerificationForm) {
		$this->kinshipVerificationForm = $kinshipVerificationForm;
	}

	public function actionDefault() {


	}

	public function createComponentKinshipVerificationForm() {
		$form = $this->kinshipVerificationForm->create();
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

}