<?php

namespace App\AdminModule\Presenters;

use App\Model\LitterApplicationRepository;

class LitterApplicationPresenter extends SignPresenter {

	/** @var LitterApplicationRepository */
	private $litterApplicationRepository;

	public function __construct(LitterApplicationRepository $litterApplicationRepository) {
		$this->litterApplicationRepository = $litterApplicationRepository;
	}


	public function actionDefault($id) {
		$applications = $this->litterApplicationRepository->findLitterApplications();

	}
}