<?php

namespace App\FrontendModule\Presenters;

use App\Enum\StateEnum;
use App\Model\UserRepository;

class FeItem1velord5Presenter extends FrontendPresenter {

	/** @var UserRepository  */
	private $userRepository;

	public function __construct(UserRepository $userRepository) {
		$this->userRepository = $userRepository;
	}

	public function actionDefault() {
		$this->template->users = $this->userRepository->findCatteries();
		$this->template->stateEnum = new StateEnum();
	}
}