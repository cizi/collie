<?php

namespace App\AdminModule\Presenters;


use App\Forms\EnumerationForm;
use App\Model\Entity\EnumerationItemEntity;
use App\Model\EnumerationRepository;

class EnumerationPresenter extends SignPresenter {

	/** @var EnumerationRepository */
	private $enumerationRepository;

	/** @var  EnumerationForm */
	private $enumerationForm;

	public function __construct(EnumerationRepository $enumerationRepository, EnumerationForm $enumerationForm) {
		$this->enumerationRepository = $enumerationRepository;
		$this->enumerationForm = $enumerationForm;
	}

	public function actionDefault() {
		$this->template->enums = $this->enumerationRepository->findEnums($this->langRepository->getCurrentLang($this->session));
	}

	public function actionDelete($id) {

	}
	public function actionDeleteItem($headerId, $order) {

	}
	public function actionEditItem($headerId, $order) {

	}

	/**
	 * @param int $id
	 */
	public function actionEdit($id) {
		$this->template->langs = $this->langRepository->findLanguages();
		$this->template->enumerationRepository = $this->enumerationRepository;
		if ($id != null) {
			foreach($this->langRepository->findLanguages() as $lang) {
				$desc = $this->enumerationRepository->getEnumDescription($id, $lang);
				$data[$lang] = [ 'description' => $desc->getDescription() ];
				$this['enumerationForm']->setDefaults($data);
			}
			$this->template->enumItems = $this->enumerationRepository->findEnumItems($this->langRepository->getCurrentLang($this->session), $id);
		} else {
			$this->template->enumItems = [];
		}
	}

	public function createComponentEnumerationForm() {
		$form = $this->enumerationForm->create($this->langRepository->findLanguages(), $this->link("default"));
		return $form;
	}
	
}