<?php

namespace App\FrontendModule\Presenters;

use App\Enum\UserRoleEnum;
use App\Forms\KinshipVerificationForm;
use App\Model\DogRepository;
use Nette\Forms\Form;

class FeItem1velord3Presenter extends FrontendPresenter {

	/** @var  KinshipVerificationForm */
	private $kinshipVerificationForm;

	/** @var  DogRepository */
	private $dogRepository;

	public function __construct(
		KinshipVerificationForm $kinshipVerificationForm,
		DogRepository $dogRepository
	) {
		$this->kinshipVerificationForm = $kinshipVerificationForm;
		$this->dogRepository = $dogRepository;
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
		//$this->template->coef = $this->dogRepository->genealogRelationship($pID, $fID);

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
}