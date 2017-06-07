<?php

namespace App\FrontendModule\Presenters;

use App\Model\DogRepository;
use App\Model\EnumerationRepository;
use App\Model\LitterApplicationRepository;
use Nette\Utils\DateTime;

class FeItem1velord4Presenter extends FrontendPresenter {

	/** @var LitterApplicationRepository */
	private $litterApplicationRepository;

	/** @var EnumerationRepository */
	private $enumRepository;

	/** @var DogRepository */
	private $dogRepository;

	public function __construct(LitterApplicationRepository $litterApplicationRepository, EnumerationRepository $enumerationRepository, DogRepository $dogRepository) {
		$this->litterApplicationRepository = $litterApplicationRepository;
		$this->enumRepository = $enumerationRepository;
		$this->dogRepository = $dogRepository;
	}


	public function actionDefault() {
		$applications = $this->litterApplicationRepository->findLitterApplications();
		$this->template->currentLang = $this->langRepository->getCurrentLang($this->session);
		$this->template->enumRepo = $this->enumRepository;
		$this->template->dogRepo = $this->dogRepository;
		$this->template->applications = $applications;

		$formData = [];
		foreach($applications as $application) {
			$data = $application->getDataDecoded();
			$formData[$application->getID()]['males'] = (isset($data['porozenoPsu']) ? $data['porozenoPsu'] : "-");
			$formData[$application->getID()]['females'] = (isset($data['porozenoFen']) ? $data['porozenoFen'] : "-");
			$formData[$application->getID()]['chs'] = (isset($data['chs']) ? $data['chs'] : "");
			$formData[$application->getID()]['birth'] = (isset($data['datumnarozeni']) ? new DateTime($data['datumnarozeni']) : null);
		}
		$this->template->formData = $formData;
	}
	
}