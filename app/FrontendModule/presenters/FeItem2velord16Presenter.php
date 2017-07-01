<?php

namespace App\FrontendModule\Presenters;

use App\Forms\MatingListDetailForm;
use App\Forms\MatingListForm;
use App\Model\DogRepository;
use App\Model\EnumerationRepository;
use App\Model\UserRepository;
use Dibi\Exception;
use Nette\Application\AbortException;
use Nette\Forms\Form;
use Nette\Utils\ArrayHash;

class FeItem2velord16Presenter extends FrontendPresenter {

	/** @var  MatingListForm */
	private $matingListForm;

	/** @var  DogRepository */
	private $dogRepository;

	/** @var  MatingListDetailForm */
	private $matingListDetailForm;

	/** @var  EnumerationRepository */
	private $enumerationRepository;

	/** @var UserRepository */
	private $userRepository;

	public function __construct(MatingListForm $matingListForm, DogRepository $dogRepository, MatingListDetailForm $matingListDetailForm, EnumerationRepository $enumerationRepository, UserRepository $userRepository) {
		$this->matingListForm = $matingListForm;
		$this->dogRepository = $dogRepository;
		$this->matingListDetailForm = $matingListDetailForm;
		$this->enumerationRepository = $enumerationRepository;
		$this->userRepository = $userRepository;
	}

	public function actionDefault() {
		if ($this->getUser()->isLoggedIn() == false) { // pokud nejsen přihlášen nemám tady co dělat
			$this->flashMessage(DOG_TABLE_DOG_ACTION_NOT_ALLOWED, "alert-danger");
			$this->redirect("Homepage:Default");
		}
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
		$form = $this->matingListDetailForm->create($this->langRepository->getCurrentLang($this->session), $this->link("default"));
		$form->onSubmit[] = $this->submitMatingListDetail;

		return $form;
	}

	public function submitMatingList(Form $form) {
		$values = $form->getHttpData();
		if (!empty($values['cID']) && !empty($values['pID']) && !empty($values['fID'])) {
			$this->redirect("details", [$values['cID'], $values['pID'], $values['fID']]);
		}
;	}

	/**
	 * @param Form $form
	 */
	public function submitMatingListDetail(Form $form) {
		if ($this->getUser()->isLoggedIn() == false) { // pokud nejsen přihlášen nemám tady co dělat
			$this->flashMessage(DOG_TABLE_DOG_ACTION_NOT_ALLOWED, "alert-danger");
			$this->redirect("Homepage:Default");
		}
		try {
			$currentLang = $this->langRepository->getCurrentLang($this->session);
			$latte = new \Latte\Engine();
			$latte->setTempDirectory(__DIR__ . '/../../../temp/cache');

			$latteParams = [];
			foreach ($form->getValues() as $inputName => $value) {
				if ($value instanceof ArrayHash) {
					foreach ($value as $dogInputName => $dogValue) {
						if ($dogInputName == 'Barva') {
							$latteParams[$inputName . $dogInputName] = $this->enumerationRepository->findEnumItemByOrder($currentLang,
								$dogValue);
						} else {
							$latteParams[$inputName . $dogInputName] = $dogValue;
						}
					}
				} else {
					if ($inputName == 'Plemeno') {
						$latteParams[$inputName] = $this->enumerationRepository->findEnumItemByOrder($currentLang, $value);
					} else {
						$latteParams[$inputName] = $value;
					}
				}
			}
			$latteParams['basePath'] = $this->getHttpRequest()->getUrl()->basePath;
			$latteParams['title'] = $this->enumerationRepository->findEnumItemByOrder($currentLang,
				$form->getValues()['cID']);

			$template = $latte->renderToString(__DIR__ . '/../templates/FeItem2velord16/pdf.latte', $latteParams);

			$pdf = new \Joseki\Application\Responses\PdfResponse($template);
			$pdf->documentTitle = MATING_FORM_CLUB . "_" . date("Y-m-d_His");
			$this->sendResponse($pdf);
		} catch (AbortException $e) {
			throw $e;
		} catch (\Exception $e) {
		}
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
		$this['matingListDetailForm']['cID']->setDefaultValue($cID);
		$this['matingListDetailForm']['pID']->setDefaults($pes->extract());
		$this['matingListDetailForm']['pID']['Jmeno']->setDefaultValue(trim($pes->getTitulyPredJmenem() . " " . $pes->getJmeno() . " " . $pes->getTitulyZaJmenem()));

		$maleOwnersToInput = "";
		$maleOwners = $this->userRepository->findDogOwnersAsUser($pes->getID());
		for($i=0; $i<count($maleOwners); $i++) {
			$maleOwnersToInput .= $maleOwners[$i]->getFullName();
			$maleOwnersToInput .= (($i+1) != count($maleOwners) ? ", " : "");
		}
		$this['matingListDetailForm']['MajitelPsa']->setDefaultValue($maleOwnersToInput);

		$fena = $this->dogRepository->getDog($fID);
		$this['matingListDetailForm']['fID']->setDefaults($fena->extract());
		$this['matingListDetailForm']['fID']['Jmeno']->setDefaultValue(trim($fena->getTitulyPredJmenem() . " " . $fena->getJmeno() . " " . $fena->getTitulyZaJmenem()));
		if ($fena->getPlemeno() != null) {
			$this['matingListDetailForm']['Plemeno']->setDefaultValue($fena->getPlemeno());
		}

		$femaleOwnersForInput = "";
		$femaleOwners = $this->userRepository->findDogOwnersAsUser($fena->getID());
		for($i=0; $i<count($femaleOwners); $i++) {
			$femaleOwnersForInput .= $femaleOwners[$i]->getFullName();
			$femaleOwnersForInput .= (($i+1) != count($femaleOwners) ? ", " : "");
		}
		$this['matingListDetailForm']['MajitelFeny']->setDefaultValue($femaleOwnersForInput);

		$this->template->title = $this->enumerationRepository->findEnumItemByOrder($this->langRepository->getCurrentLang($this->session), $cID);
		$this->template->cID = $cID;
	}
	
}