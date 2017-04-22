<?php

namespace App\FrontendModule\Presenters;

use App\Forms\MatingListDetailForm;
use App\Forms\MatingListForm;
use App\Model\DogRepository;
use Nette\Forms\Form;

class FeItem2velord16Presenter extends FrontendPresenter {

	/** @var  MatingListForm */
	private $matingListForm;

	/** @var  DogRepository */
	private $dogRepository;

	/** @var  MatingListDetailForm */
	private $matingListDetailForm;

	public function __construct(MatingListForm $matingListForm, DogRepository $dogRepository, MatingListDetailForm $matingListDetailForm) {
		$this->matingListForm = $matingListForm;
		$this->dogRepository = $dogRepository;
		$this->matingListDetailForm = $matingListDetailForm;
	}

	public function createComponentMatingListForm() {
		$form = $this->matingListForm->create($this->langRepository->getCurrentLang($this->session));
		$form->onSubmit[] = $this->submitMatingList;

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

	public function createComponentMatingListDetailForm() {
		$form = $this->matingListDetailForm->create($this->langRepository->getCurrentLang($this->session), $this->link(("default")));
		$form->onSubmit[] = $this->submitMatingListDetail;

		return $form;
	}

	public function submitMatingList(Form $form) {
		$values = $form->getHttpData();
		if (!empty($values['cID']) && !empty($values['pID']) && !empty($values['fID'])) {
			$this->redirect("details", [$values['cID'], $values['pID'], $values['fID']]);
		}
;	}

	public function submitMatingListDetail(Form $form) {
		$values = $form->getHttpData();
		if (!empty($values['cID']) && !empty($values['pID']) && !empty($values['fID'])) {
			$this->redirect("details", [$values['cID'], $values['pID'], $values['fID']]);
		}
	}

	/**
	 * @param int $cID
	 * @param int $pID
	 * @param int $fID
	 */
	public function actionDetails($cID, $pID, $fID) {
		$pes = $this->dogRepository->getDog($pID);
		$this['matingListDetailForm']['pID']->setDefaults($pes->extract());

		$fena = $this->dogRepository->getDog($fID);
		$this['matingListDetailForm']['fID']->setDefaults($fena->extract());

		$this->template->cID = $cID;
	}
	
}