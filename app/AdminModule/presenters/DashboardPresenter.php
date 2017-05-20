<?php

namespace App\AdminModule\Presenters;

use App\AdminModule\Presenters;
use App\Model\AwaitingChangesRepository;
use App\Model\DogRepository;
use App\Model\UserRepository;

class DashboardPresenter extends SignPresenter {

	/** @var AwaitingChangesRepository */
	private $awaitingRepository;

	/** @var UserRepository */
	private $userRepository;

	/** @var DogRepository */
	private $dogRepository;

	public function __construct(
		AwaitingChangesRepository $awaitingChangesRepository,
		UserRepository $userRepository,
		DogRepository $dogRepository
	) {
		$this->awaitingRepository = $awaitingChangesRepository;
		$this->userRepository = $userRepository;
		$this->dogRepository = $dogRepository;
	}

	public function actionDefault() {
		$this->template->awaitingChanges = $this->awaitingRepository->findAwaitingChanges();
		$this->template->userRepository = $this->userRepository;
		$this->template->dogRepository = $this->dogRepository;
	}


	public function actionProceedChange($id) {

	}

	public function actionDeclineChange($id) {

	}

}