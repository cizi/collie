<?php

namespace App\AdminModule\Presenters;


use App\Model\EnumerationRepository;

class EnumerationPresenter extends SignPresenter {

	/** @var EnumerationRepository */
	private $enumerationRepository;

	public function __construct(EnumerationRepository $enumerationRepository) {
		$this->enumerationRepository = $enumerationRepository;
	}

	public function actionDefault() {
		$this->template->enums = $this->enumerationRepository->findEnums($this->langRepository->getCurrentLang($this->session));
	}

	public function actionDelete($id) {

	}

	public function actionEdit($id) {

	}
	
}