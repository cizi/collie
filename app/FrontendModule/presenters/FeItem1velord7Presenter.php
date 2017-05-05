<?php

namespace App\FrontendModule\Presenters;

use App\Model\DogRepository;
use App\Model\EnumerationRepository;
use App\Model\ShowDogRepository;
use App\Model\ShowRefereeRepository;
use App\Model\ShowRepository;

class FeItem1velord7Presenter extends FrontendPresenter {

	/** @var  ShowRepository */
	private $showRepository;

	/** @var  EnumerationRepository */
	private $enumerationRepository;

	/** @var  ShowRefereeRepository */
	private $showRefereeRepository;

	/** @var ShowDogRepository  */
	private $showDogRepository;

	/** @var DogRepository  */
	private $dogRepository;

	public function __construct(
		ShowRepository $showRepository,
		EnumerationRepository $enumerationRepository,
		ShowRefereeRepository $showRefereeRepository,
		ShowDogRepository $showDogRepository,
		DogRepository $dogRepository
	) {
		$this->showRepository = $showRepository;
		$this->enumerationRepository = $enumerationRepository;
		$this->showRefereeRepository = $showRefereeRepository;
		$this->showDogRepository = $showDogRepository;
		$this->dogRepository = $dogRepository;
	}

	public function startup() {
		parent::startup();
		$this->template->enumRepo = $this->enumerationRepository;
		$this->template->lang = $this->langRepository->getCurrentLang($this->session);
		$this->template->showRefereeRepository = $this->showRefereeRepository;
		$this->template->showDogRepository = $this->showDogRepository;
		$this->template->dogRepository = $this->dogRepository;
	}

	public function actionDefault() {
		$this->template->shows = $this->showRepository->findShows();

	}

	/**
	 * @param int $id
	 */
	public function actionDetail($id) {
		$this->template->show = $this->showRepository->getShow($id);
		$this->template->referees = $this->showRefereeRepository->findRefereeByShow($id);
		$this->template->dogs = $this->showDogRepository->findDogsByShow($id);
	}
}