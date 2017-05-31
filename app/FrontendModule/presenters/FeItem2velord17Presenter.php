<?php

namespace App\FrontendModule\Presenters;

use App\Forms\LitterApplicationDetailForm;
use App\Forms\LitterApplicationForm;
use App\Model\DogRepository;
use App\Model\EnumerationRepository;
use Nette\Forms\Form;

class FeItem2velord17Presenter extends FrontendPresenter {

	/** @var  LitterApplicationForm */
	private $litterApplicationForm;

	/** @var  DogRepository */
	private $dogRepository;

	/** @var  LitterApplicationDetailForm */
	private $litterApplicationDetailForm;

	/** @var  EnumerationRepository */
	private $enumerationRepository;

	public function __construct(
		LitterApplicationForm $litterApplicationForm,
		DogRepository $dogRepository,
		LitterApplicationDetailForm $litterApplicationDetailForm,
		EnumerationRepository $enumerationRepository
	) {
		$this->litterApplicationForm = $litterApplicationForm;
		$this->dogRepository = $dogRepository;
		$this->litterApplicationDetailForm = $litterApplicationDetailForm;
		$this->enumerationRepository = $enumerationRepository;
	}

	public function actionDefault() {

	}

	public function createComponentLitterApplicationForm() {
		$form = $this->litterApplicationForm->create($this->langRepository->getCurrentLang($this->session));
		$form->onSuccess[] = $this->verifyLitterApplication;

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

	public function createComponentLitterApplicationDetailForm() {
		$form = $this->litterApplicationDetailForm->create($this->langRepository->getCurrentLang($this->session), $this->link("default"));
		$form->onSuccess[] = $this->verifyLitterApplicationDetail;

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

	/**
	 * @param Form $form
	 */
	public function verifyLitterApplication(Form $form) {
		$values = $form->getHttpData();
		if (!empty($values['pID']) && !empty($values['fID']) && !empty($values['cID'])) {
			$this->redirect("details", [$values['cID'], $values['pID'], $values['fID']]);
		}
	}

	/**
	 * @param Form $form
	 */
	public function verifyLitterApplicationDetail(Form $form) {

	}

	/**
	 * @param int $cID
	 * @param int $pID
	 * @param int $fID
	 */
	public function actionDetails($cID, $pID, $fID) {
		if ($this->getUser()->isLoggedIn() == false) { // pokud nejsen přihlášen nemám tady co dělat
			$this->flashMessage(DOG_TABLE_DOG_ACTION_NOT_ALLOWED, "alert-danger");
			$this->redirect("Homepage:Default");
		}
		$pes = $this->dogRepository->getDog($pID);
		/*
		$this['matingListDetailForm']['cID']->setDefaultValue($cID);
		$this['matingListDetailForm']['pID']->setDefaults($pes->extract());
		$this['matingListDetailForm']['pID']['Jmeno']->setDefaultValue(trim($pes->getTitulyPredJmenem() . " " . $pes->getJmeno() . " " . $pes->getTitulyZaJmenem()));

		$fena = $this->dogRepository->getDog($fID);
		$this['matingListDetailForm']['fID']->setDefaults($fena->extract());
		$this['matingListDetailForm']['fID']['Jmeno']->setDefaultValue(trim($fena->getTitulyPredJmenem() . " " . $fena->getJmeno() . " " . $fena->getTitulyZaJmenem()));
		*/

		$this->template->title = $this->enumerationRepository->findEnumItemByOrder($this->langRepository->getCurrentLang($this->session), $cID);
		$this->template->cID = $cID;
	}

}