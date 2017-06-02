<?php

namespace App\FrontendModule\Presenters;

use App\Enum\LitterApplicationStateEnum;
use App\Forms\LitterApplicationDetailForm;
use App\Forms\LitterApplicationForm;
use App\Model\DogRepository;
use App\Model\Entity\DogEntity;
use App\Model\Entity\LitterApplicationEntity;
use App\Model\EnumerationRepository;
use App\Model\LitterApplicationRepository;
use Dibi\DateTime;
use Nette\Application\AbortException;
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

	/** @var LitterApplicationRepository */
	private $litterApplicationRepository;

	/**
	 * FeItem2velord17Presenter constructor.
	 * @param LitterApplicationForm $litterApplicationForm
	 * @param DogRepository $dogRepository
	 * @param LitterApplicationDetailForm $litterApplicationDetailForm
	 * @param EnumerationRepository $enumerationRepository
	 * @param LitterApplicationRepository $litterApplicationRepository
	 */
	public function __construct(
		LitterApplicationForm $litterApplicationForm,
		DogRepository $dogRepository,
		LitterApplicationDetailForm $litterApplicationDetailForm,
		EnumerationRepository $enumerationRepository,
		LitterApplicationRepository $litterApplicationRepository
	) {
		$this->litterApplicationForm = $litterApplicationForm;
		$this->dogRepository = $dogRepository;
		$this->litterApplicationDetailForm = $litterApplicationDetailForm;
		$this->enumerationRepository = $enumerationRepository;
		$this->litterApplicationRepository = $litterApplicationRepository;
	}

	public function createComponentLitterApplicationForm() {
		$form = $this->litterApplicationForm->create($this->langRepository->getCurrentLang($this->session));
		$form->onSubmit[] = $this->verifyLitterApplication;

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
		$form->onSubmit[] = $this->submitLitterApplicationDetail;

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
	public function submitLitterApplicationDetail(Form $form) {
		if ($this->getUser()->isLoggedIn() == false) { // pokud nejsen přihlášen nemám tady co dělat
			$this->flashMessage(DOG_TABLE_DOG_ACTION_NOT_ALLOWED, "alert-danger");
			$this->redirect("Homepage:Default");
		}
		try {
			$array = $form->getHttpData();
			$litterApplicationEntity = new LitterApplicationEntity();
			$litterApplicationEntity->hydrate($array);

			$latteParams = $array;
			$latteParams['basePath'] = $this->getHttpRequest()->getUrl()->basePath;
			$latteParams['puppiesLines'] = LitterApplicationDetailForm::NUMBER_OF_LINES;
			$latteParams['enumRepository'] = $this->enumerationRepository;
			$latteParams['currentLang'] = $this->langRepository->getCurrentLang($this->session);

			$latte = new \Latte\Engine();
			$latte->setTempDirectory(__DIR__ . '/../../../temp/cache');
			$template = $latte->renderToString(__DIR__ . '/../templates/FeItem2velord17/pdf.latte', $latteParams);

			$data = base64_encode(gzdeflate(serialize($_POST)));
			$litterApplicationEntity->setData($data);
			$formular = base64_encode(gzdeflate($template));
			$litterApplicationEntity->setFormular($formular);
			$litterApplicationEntity->setDatum(new DateTime());
			$litterApplicationEntity->setDatumKryti(new DateTime($array["datumkryti"]));	// srovnání indexu DB vs formulář
			$litterApplicationEntity->setZavedeno(LitterApplicationStateEnum::INSERT);
			if ($litterApplicationEntity->getPlemeno() == 0) {
				$litterApplicationEntity->setPlemeno(null);
			}

			$this->litterApplicationRepository->save($litterApplicationEntity);

			$pdf = new \Joseki\Application\Responses\PdfResponse($template);
			$pdf->documentTitle = LITTER_APPLICATION . "_" . date("Y-m-d_His");
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
		$title = $this->enumerationRepository->findEnumItemByOrder($this->langRepository->getCurrentLang($this->session), $cID);

		// nastavíme hidny
		$this['litterApplicationDetailForm']['cID']->setDefaultValue($cID);
		$this['litterApplicationDetailForm']['title']->setDefaultValue($title);

		$this['litterApplicationDetailForm']['oID']->setDefaultValue($pID);
		$this['litterApplicationDetailForm']['mID']->setDefaultValue($fID);
		$clubName = $this->enumerationRepository->findEnumItemByOrder($this->langRepository->getCurrentLang($this->session), $cID);
		$this['litterApplicationDetailForm']['Klub']->setDefaultValue($clubName);

		$pes = $this->dogRepository->getDog($pID);
		$name = trim($pes->getTitulyPredJmenem() . " " . $pes->getJmeno() . " " . $pes->getTitulyZaJmenem());
		$this['litterApplicationDetailForm']['otec']->setDefaultValue($name);
		$this['litterApplicationDetailForm']['otecBarva']->setDefaultValue($pes->getBarva());
		$this['litterApplicationDetailForm']['otecSrst']->setDefaultValue($pes->getSrst());
		$this['litterApplicationDetailForm']['otecBon']->setDefaultValue($pes->getBonitace());
		$this['litterApplicationDetailForm']['otecHeight']->setDefaultValue($pes->getVyska());
		if ($pes->getDatNarozeni() != null) {
			$this['litterApplicationDetailForm']['otecDN']->setDefaultValue($pes->getDatNarozeni()->format(DogEntity::MASKA_DATA));
		}

		$fena = $this->dogRepository->getDog($fID);
		$name = trim($fena->getTitulyPredJmenem() . " " . $fena->getJmeno() . " " . $fena->getTitulyZaJmenem());
		$this['litterApplicationDetailForm']['matka']->setDefaultValue($name);
		$this['litterApplicationDetailForm']['matkaBarva']->setDefaultValue($fena->getBarva());
		$this['litterApplicationDetailForm']['matkaSrst']->setDefaultValue($fena->getSrst());
		$this['litterApplicationDetailForm']['matkaBon']->setDefaultValue($fena->getBonitace());
		$this['litterApplicationDetailForm']['matkaHeight']->setDefaultValue($fena->getVyska());
		if ($fena->getDatNarozeni() != null) {
			$this['litterApplicationDetailForm']['matkaDN']->setDefaultValue($fena->getDatNarozeni()->format(DogEntity::MASKA_DATA));
		}

		$this->template->puppiesLines = LitterApplicationDetailForm::NUMBER_OF_LINES;
		$this->template->title = $title;
		$this->template->cID = $cID;
	}
}