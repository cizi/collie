<?php

namespace App\FrontendModule\Presenters;

use App\Forms\LitterApplicationDetailForm;
use App\Forms\LitterApplicationForm;
use App\Model\DogRepository;
use App\Model\Entity\DogEntity;
use App\Model\Entity\LitterApplicationEntity;
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
		$array = $form->getHttpData();
		$litterApplicationEntity = new LitterApplicationEntity();
		$litterApplicationEntity->hydrate($array);

		$data = base64_encode(gzdeflate(serialize($_POST)));
		$litterApplicationEntity->setData($data);

		$latte = new \Latte\Engine();
		$latte->setTempDirectory(__DIR__ . '/../../../temp/cache');

		$latteParams = $array;
		$latteParams['basePath'] = $this->getHttpRequest()->getUrl()->basePath;
		$latteParams['puppiesLines'] = LitterApplicationDetailForm::NUMBER_OF_LINES;

		$template = $latte->renderToString(__DIR__ . '/../templates/FeItem2velord17/pdf.latte', $latteParams);
		dump($template); die;



		//$forma = "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";
		//$a = gzinflate(base64_decode($forma));
		// $formular = base64_encode(gzdeflate($contents));

		// $a = sprintf($form);
		dump($litterApplicationEntity, $array); die;
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