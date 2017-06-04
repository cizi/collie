<?php

namespace App\AdminModule\Presenters;

use App\Enum\LitterApplicationStateEnum;
use App\Forms\LitterApplicationDetailForm;
use App\Forms\LitterApplicationRewriteForm;
use App\Model\DogRepository;
use App\Model\Entity\BreederEntity;
use App\Model\Entity\DogEntity;
use App\Model\EnumerationRepository;
use App\Model\LitterApplicationRepository;
use Nette\Application\AbortException;
use Nette\Forms\Form;

class LitterApplicationPresenter extends SignPresenter {

	/** @var LitterApplicationRepository */
	private $litterApplicationRepository;

	/** @var EnumerationRepository */
	private $enumerationRepository;

	/** @var DogRepository */
	private $dogRepository;

	/** @var LitterApplicationRewriteForm */
	private $litterApplicationRewriteForm;

	public function __construct(
		LitterApplicationRepository $litterApplicationRepository,
		EnumerationRepository $enumerationRepository,
		DogRepository $dogRepository,
		LitterApplicationRewriteForm $applicationRewriteForm,
		LitterApplicationRewriteForm $litterApplicationRewriteForm
	) {
		$this->litterApplicationRepository = $litterApplicationRepository;
		$this->enumerationRepository = $enumerationRepository;
		$this->dogRepository = $dogRepository;
		$this->litterApplicationRewriteForm = $litterApplicationRewriteForm;
	}

	/**
	 * @param int $id
	 */
	public function actionDefault($id) {
		$this->template->applications = $this->litterApplicationRepository->findLitterApplications();
		$this->template->enumRepo = $this->enumerationRepository;
		$this->template->dogRepo = $this->dogRepository;
		$this->template->currentLang = $this->langRepository->getCurrentLang($this->session);
		$this->template->litterApplicationStateEnum =  LitterApplicationStateEnum::class;
	}

	/**
	 * @param int $id
	 */
	public function actionDelete($id) {
		if ($this->litterApplicationRepository->delete($id)) {
			$this->flashMessage(LITTER_APPLICATION_DELETED, "alert-success");
		} else {
			$this->flashMessage(LITTER_APPLICATION_DELETED_FAILED, "alert-danger");
		}
		$this->redirect("default");
	}

	/**
	 * @param int $id
	 */
	public function actionRewriteDescendants($id) {
		$application = $this->litterApplicationRepository->getLitterApplication($id);
		if ($application != null) {
			if ($application->getZavedeno() == LitterApplicationStateEnum::REWRITTEN) {
				$this->flashMessage(LITTER_APPLICATION_REWRITE_DESCENDANTS_ALREADY_IN, "alert-danger");
				$this->redirect("default");
			}
			$appParams = $application->getDataDecoded();
			$formData["Plemeno"] = $appParams["Plemeno"];
			$formData["mID"] = $appParams["mID"];
			$formData["oID"] = $appParams["oID"];
			$formData["ID"] = $id;
			if (trim($appParams["datumnarozeni"]) != "") {
				$formData["DatNarozeni"] = $appParams["datumnarozeni"];
			}
			for($i = 1; $i <= LitterApplicationDetailForm::NUMBER_OF_LINES; $i++) {
				$formData[$i]["Cip"] = $this->getValueByKeyFromArray($appParams, $i, "mikrocip");
				$formData[$i]["Jmeno"] = $this->getValueByKeyFromArray($appParams, $i, "jmeno");
				$formData[$i]["Pohlavi"] =$this->getValueByKeyFromArray($appParams, $i, "pohlavi", true);
				$formData[$i]["Srst"] = $this->getValueByKeyFromArray($appParams, $i, "srst", true);
				$formData[$i]["Barva"] = $this->getValueByKeyFromArray($appParams, $i, "barva", true);
			}
			$this['litterApplicationRewriteForm']->setDefaults($formData);
		} else {
			$this->flashMessage(LITTER_APPLICATION_REWRITE_DOES_NOT_EXIST, "alert-danger");
			$this->redirect("default");
		}
	}

	/**
	 * @param $array
	 * @param int $lineNumber
	 * @param string $key
	 * @return string
	 */
	private function getValueByKeyFromArray($array, $lineNumber, $key, $isSelect =  false) {
		$result = "";
		foreach ($array as $arrKey => $arrValue) {
			if (($key.$lineNumber) == $arrKey) {
				$result = $arrValue;
				break;
			}
		}

		return ($isSelect && ($result == "") ? 0 : $result);
	}

	/**
	 * @return \Nette\Application\UI\Form
	 */
	public function createComponentLitterApplicationRewriteForm() {
		$currentLang = $this->langRepository->getCurrentLang($this->session);
		$form = $this->litterApplicationRewriteForm->create($currentLang);
		$form->onSubmit[] = $this->submitRewrite;

		return $form;
	}

	/**
	 * @param Form $form
	 */
	public function submitRewrite(Form $form) {
		try {
			$formArray = $form->getHttpData();
			$breeders = [];
			$dogs = [];

			if (isset($formArray['breeder'])) {	// chovatele
				$breederEntity = new BreederEntity();
				$breederEntity->hydrate($formArray['breeder']);
				$breeders[] = $breederEntity;
			}
			unset($formArray['breeder']);

			foreach ($formArray as $dogArr) { // psi
				if (is_array($dogArr)) {
					$dogEntity = new DogEntity();
					$dogEntity->hydrate($dogArr);
					if (($dogEntity->getJmeno() == "") && ($dogEntity->getCip() == "")) {
						continue;
					}
					if ($formArray["Plemeno"] != 0) {
						$dogEntity->setPlemeno($formArray["Plemeno"]);
					}
					$dogEntity->setMID($formArray["mID"]);
					$dogEntity->setOID($formArray["oID"]);
					if ($formArray["DatNarozeni"] != "") {
						$dogEntity->setDatNarozeni($formArray["DatNarozeni"]);
					}
					$dogs[] = $dogEntity;
				}
			}
			$application = $this->litterApplicationRepository->getLitterApplication($formArray['ID']);
			$this->dogRepository->saveDescendants($dogs, $breeders, $application);

			$this->flashMessage(LITTER_APPLICATION_REWRITE_DESCENDANTS_OK, "alert-success");
			$this->redirect("default");
		} catch (AbortException $e) {
			throw $e;
		} catch (\Exception $e) {
			$this->flashMessage(LITTER_APPLICATION_REWRITE_DESCENDANTS_FAILED, "alert-danger");
		}
	}
}