<?php

namespace App\FrontendModule\Presenters;

class FeItem2velord13Presenter extends BasePresenter {

	/**
	 * Odhl�en�
	 */
	public function actionDefault() {
		$this->getUser()->logout();
		$this->flashMessage(ADMIN_LOGIN_UNLOGGED, "alert-success");
		$this->redirect('Homepage:default');
	}
}