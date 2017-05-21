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
		$this->template->proceededChanges = $this->awaitingRepository->findProceededChanges();
		$this->template->declinedChanges = $this->awaitingRepository->findDeclinedChanges();
		$this->template->userRepository = $this->userRepository;
		$this->template->dogRepository = $this->dogRepository;
	}

	/**
	 * @param int $id
	 */
	public function actionProceedChange($id) {
		$awaitingChange = $this->awaitingRepository->getAwaitingChange($id);
		if ($awaitingChange != null) {
			try {
				$this->awaitingRepository->proceedChange($awaitingChange, $this->getUser());
				$this->flashMessage(AWAITING_CHANGE_CHANGE_ACCEPT, "alert-success");
			} catch (\Exception $e) {
				$this->flashMessage(AWAITING_CHANGE_CHANGE_ERR, "alert-danger");
			}
		}
		$this->redirect("default");
	}

	/**
	 * @param int $id
	 */
	public function actionDeclineChange($id) {
		$awaitingChange = $this->awaitingRepository->getAwaitingChange($id);
		if ($awaitingChange != null) {
			try {
				$this->awaitingRepository->declineChange($awaitingChange, $this->getUser());
				$this->flashMessage(AWAITING_CHANGE_CHANGE_DECLINE, "alert-success");
			} catch (\Exception $e) {
				$this->flashMessage(AWAITING_CHANGE_CHANGE_ERR, "alert-danger");
			}
		}
		$this->redirect("default");
	}

}