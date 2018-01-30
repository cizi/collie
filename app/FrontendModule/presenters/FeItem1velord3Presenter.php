<?php

namespace App\FrontendModule\Presenters;

use App\Enum\UserRoleEnum;
use App\Forms\KinshipVerificationForm;
use App\Model\DogRepository;
use App\Model\EnumerationRepository;
use Nette\Application\Responses;
use Nette\Forms\Form;

class FeItem1velord3Presenter extends FrontendPresenter {

	/** @var  KinshipVerificationForm */
	private $kinshipVerificationForm;

	/** @var  DogRepository */
	private $dogRepository;

	/** @var  EnumerationRepository */
	private $enumRepository;

	public function __construct(
		KinshipVerificationForm $kinshipVerificationForm,
		DogRepository $dogRepository,
		EnumerationRepository $enumerationRepository
	) {
		$this->kinshipVerificationForm = $kinshipVerificationForm;
		$this->dogRepository = $dogRepository;
		$this->enumRepository = $enumerationRepository;
	}

	public function actionDefault() {

	}

	public function createComponentKinshipVerificationForm() {
		$form = $this->kinshipVerificationForm->create();

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
	 * @param int $pID
	 * @param int $fID
	 * @param int $urovne
	 */
	public function actionVerifyKinship($pID, $fID, $urovne = 4) {
		$lang = $this->langRepository->getCurrentLang($this->session);
		$amIAdmin = ($this->getUser()->isLoggedIn() && $this->getUser()->getRoles()[0] == UserRoleEnum::USER_ROLE_ADMINISTRATOR);

		$this->template->male = $this->dogRepository->getDog($pID);
		$this->template->female = $this->dogRepository->getDog($fID);
		$this->template->coef = $this->dogRepository->genealogRelationship($pID, $fID);

		$deepMark = true;
		$this->template->genLev = $urovne;

		global $pedigree;
		// $this->template->malePedigree =
		$this->dogRepository->genealogDeepPedigreeV2($pID, $urovne, $lang, $this->presenter, $amIAdmin, $deepMark);
		$allDogsInPedigree = $pedigree;

		// $this->template->femalePedigree =
		$this->dogRepository->genealogDeepPedigreeV2($fID, $urovne, $lang, $this->presenter, $amIAdmin, $deepMark);
		$allDogsInPedigree = array_merge($allDogsInPedigree, $pedigree);

		$this->dogRepository->selectRepeatingDogs($pID, $fID, $allDogsInPedigree);
		$this->template->femalePedigree = $this->dogRepository->genealogDeepPedigreeV2($fID, $urovne, $lang, $this->presenter, $amIAdmin, $deepMark);
		$this->template->malePedigree = $this->dogRepository->genealogDeepPedigreeV2($pID, $urovne, $lang, $this->presenter, $amIAdmin, $deepMark);

		$this->template->avk = $this->dogRepository->genealogAvk($allDogsInPedigree);
	}

	/**
	 * AJAX - podle příchozího ID psa načtu jeho jméno, obrázek a zdravotní vyšetření
	 */
	public function handleDogSwitch() {
		$data = $this->request->getParameters();
		$idDog = $data['idDog'];
		$lang = $this->langRepository->getCurrentLang($this->session);

		$response = "";
		$dog = $this->dogRepository->getDog($idDog);
		if ($dog != null) {
			$response .= "<b>" . trim($dog->getTitulyPredJmenem() . " " . $dog->getJmeno() . " " . $dog->getTitulyZaJmenem()) . "</b>";
			// obrázky psa
			$dogPics = $this->dogRepository->findDogPics($dog->getID());
			if (!empty($dogPics)) {
				$pic = reset($dogPics);
				$response .= "<br /><img src='{$pic->getCesta()}' class='dogPicSmall' /><br />";
			}
			// zdraví psa
			$healths = $this->dogRepository->findHealthsByDogId($dog->getID());
			foreach ($healths as $health) {
				$response .= "<span style='white-space: nowrap;'>". $this->enumRepository->findEnumItemByOrder($lang, $health->getTyp()) . ": " . $health->getVysledek()."<br /></span>";
			}
			// pokud je zdraví i obrázky prázné, tak nemá smysl cokoliv vypisovat
			if (empty($dogPics) && empty($healths)) {
				$response .= "<br /><i>" . KINSHIP_VERIFICATION_NO_AJAX_DATA . "</i>";
			}
		}

		$this->sendResponse(new Responses\TextResponse($response));
		$this->terminate();
	}
}