<?php

namespace App\AdminModule\Presenters;

use App\Enum\UserRoleEnum;
use Nette;
use App\FrontendModule\Presenters\BasePresenter;

class SignPresenter extends BasePresenter {

	/**
	 * If the user is not logged in is necessary to log him in
	 */
	public function startup() {
		if($this->getUser()->isLoggedIn() == false){
			$this->redirect('Default:default');
		} else if ($this->getUser()->getRoles()[0] != UserRoleEnum::USER_ROLE_ADMINISTRATOR) {
			$this->redirect(':Frontend:Homepage:default');
		}

		parent::startup();
	}

	public function actionOut(){
		$this->getUser()->logout();
		$this->flashMessage(ADMIN_LOGIN_UNLOGGED);
		$this->redirect('default');
	}
}
