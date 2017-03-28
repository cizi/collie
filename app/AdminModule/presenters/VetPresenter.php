<?php

namespace App\AdminModule\Presenters;

use App\Forms\VetForm;
use App\Model\VetRepository;

class VetPresenter extends SignPresenter {

	/**
	 * @var VetRepository
	 */
	private $vetRepository;

	/**
	 * @var VetForm
	 */
	private $vetForm;

	public function __construct(VetRepository $vetRepository, VetForm $vetForm) {
		$this->vetRepository = $vetRepository;
		$this->vetForm = $vetForm;
	}

	public function actionDefault($id) {
		$this->template->vets = $this->vetRepository->FindVets();
	}

	public function actionDelete($id) {

	}

	public function actionEdit($id) {
		if ($id == null) {
			$this->template->vet = null;
		} else {

		}
	}

	public function createComponentEditForm() {
		$form = $this->vetForm->create($this->link("default"));

		return $form;
	}
}