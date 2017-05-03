<?php

namespace App\AdminModule\Presenters;

use App\Model\EnumerationRepository;
use App\Model\RefereeRepository;
use App\Model\ShowRepository;

class ShowPresenter extends SignPresenter {

	/** @var ShowRepository  */
	private $showRepository;

	/** @var EnumerationRepository  */
	private  $enumerationRepository;

	/** @var  RefereeRepository */
	private $refereeRepository;

	public function __construct(ShowRepository $showRepository, EnumerationRepository $enumerationRepository, RefereeRepository $refereeRepository) {
		$this->showRepository = $showRepository;
		$this->enumerationRepository = $enumerationRepository;
		$this->refereeRepository = $refereeRepository;
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

	public function actionEdit($id) {
		
		
	}

	public function actionDelete($id) {
		
	}

}