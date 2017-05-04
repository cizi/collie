<?php

namespace App\AdminModule\Presenters;

use App\Forms\ShowDogForm;
use App\Forms\ShowForm;
use App\Forms\ShowRefereeForm;
use App\Model\Entity\ShowEntity;
use App\Model\EnumerationRepository;
use App\Model\RefereeRepository;
use App\Model\ShowDogRepository;
use App\Model\ShowRefereeRepository;
use App\Model\ShowRepository;
use Nette\Application\AbortException;
use Nette\Forms\Form;

class ShowPresenter extends SignPresenter {

	/** @var ShowRepository  */
	private $showRepository;

	/** @var EnumerationRepository  */
	private  $enumerationRepository;

	/** @var  RefereeRepository */
	private $refereeRepository;

	/** @var ShowForm */
	private $showForm;

	/** @var  ShowDogForm */
	private $showDogForm;

	/** @var ShowRefereeForm  */
	private $showRefereeForm;

	/** @var  ShowDogRepository */
	private $showDogRepository;

	/** @var  ShowRefereeRepository */
	private $showRefereeRepository;

	/**
	 * @param ShowRepository $showRepository
	 * @param EnumerationRepository $enumerationRepository
	 * @param RefereeRepository $refereeRepository
	 * @param ShowForm $showForm
	 * @param ShowDogForm $showDogForm
	 * @param ShowRefereeForm $showRefereeForm
	 * @param ShowDogRepository $showDogRepository
	 * @param ShowRefereeRepository $showRefereeRepository
	 */
	public function __construct(
		ShowRepository $showRepository,
		EnumerationRepository $enumerationRepository,
		RefereeRepository $refereeRepository,
		ShowForm $showForm,
		ShowDogForm $showDogForm,
		ShowRefereeForm $showRefereeForm,
		ShowDogRepository $showDogRepository,
		ShowRefereeRepository $showRefereeRepository
	) {
		$this->showRepository = $showRepository;
		$this->enumerationRepository = $enumerationRepository;
		$this->refereeRepository = $refereeRepository;
		$this->showForm = $showForm;
		$this->showDogForm = $showDogForm;
		$this->showRefereeForm = $showRefereeForm;
		$this->showDogRepository = $showDogRepository;
		$this->showRefereeRepository = $showRefereeRepository;
	}

	public function actionDefault() {
		$this->template->lang = $this->langRepository->getCurrentLang($this->session);
		$this->template->enumRepo= $this->enumerationRepository;
		$this->template->shows = $this->showRepository->findShows();
		$this->template->refereeRepository = $this->refereeRepository;
	}

	/**
	 * Oznaèí výstavu jakko ukonèenou/neukonèenou
	 * @throws \Nette\Application\AbortException
	 */
	public function handleDoneSwitch() {
		$data = $this->request->getParameters();
		$idShow = $data['idShow'];
		$switchTo = (!empty($data['to']) && $data['to'] == "false" ? false : true);

		if ($switchTo) {
			$this->showRepository->setShowDone($idShow);
		} else {
			$this->showRepository->setShowUndone($idShow);
		}

		$this->terminate();
	}


	public function actionDetail($id) {
		
	}

	/**
	 * @param int $id
	 */
	public function actionDelete($id) {
		if ($this->showRepository->delete($id)) {
			$this->flashMessage(SHOW_DELETED, "alert-success");
		} else {
			$this->flashMessage(SHOW_DELETED_FAILED, "alert-danger");
		}
		$this->redirect('default');
	}

	public function createComponentEditForm() {
		$form = $this->showForm->create($this->link("default"), $this->langRepository->getCurrentLang($this->session));
		$form->onSuccess[] = $this->saveShow;

		return $form;
	}

	/**
	 * @param Form $form
	 */
	public function saveShow(Form $form) {
		$showEntity = new ShowEntity();
		try {
			$showEntity->hydrate($form->getHttpData());
			$this->showRepository->save($showEntity);
			$this->flashMessage(SHOW_FORM_NEW_ADDED, "alert-success");
			$this->redirect("default");
		} catch (\Exception $e) {
			if ($e instanceof AbortException) {
				throw $e;
			} else {
				$form->addError(SHOW_FORM_NEW_FAILED);
				$this->flashMessage(SHOW_FORM_NEW_FAILED, "alert-danger");
			}
		}
	}

	/**
	 * @param int $id
	 */
	public function actionEdit($id) {
		$showEntity = $this->showRepository->getShow($id);
		$this->template->show = $showEntity;
		$this->template->lang = $this->langRepository->getCurrentLang($this->session);

		if ($showEntity) {
			$this['editForm']->addHidden('ID', $showEntity->getID());
			$this['editForm']->setDefaults($showEntity->extract());
			if ($showEntity->getDatum() != null) {
				$this['editForm']['Datum']->setDefaultValue($showEntity->getDatum()->format(ShowEntity::MASKA_DATA));
			}
		}
	}

}