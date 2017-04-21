<?php

namespace App\FrontendModule\Presenters;

use App\Controller\FileController;
use App\Forms\DogFilterForm;
use App\Forms\DogForm;
use App\Model\DogRepository;
use App\Model\Entity\BreederEntity;
use App\Model\Entity\DogEntity;
use App\Model\Entity\DogHealthEntity;
use App\Model\Entity\DogOwnerEntity;
use App\Model\Entity\DogPicEntity;
use App\Model\Entity\EnumerationItemEntity;
use App\Model\EnumerationRepository;
use App\Model\UserRepository;
use Nette\Application\AbortException;
use Nette\Forms\Form;
use Nette\Http\FileUpload;
use Nette\Utils\Paginator;

class FeItem2velord11Presenter extends FrontendPresenter {

	/** @persistent */
	public $filter;

	/** @var DogRepository */
	private $dogRepository;

	/** @var DogFilterForm */
	private $dogFilterForm;

	/** @var DogForm */
	private $dogForm;

	/** @var UserRepository  */
	private $userRepository;

	/** @var EnumerationRepository  */
	private $enumerationRepository;

	public function __construct(
		DogFilterForm $dogFilterForm,
		DogForm $dogForm,
		DogRepository $dogRepository,
		EnumerationRepository $enumerationRepository,
		UserRepository $userRepository
	) {
		$this->dogFilterForm = $dogFilterForm;
		$this->dogForm = $dogForm;
		$this->dogRepository = $dogRepository;
		$this->enumerationRepository = $enumerationRepository;
		$this->userRepository = $userRepository;
	}

	/**
	 * @return array
	 */
	private function decodeFilterFromQuery() {
		$filter = [];
		if ($this->filter != "") {
			$arr = explode("&", $this->filter);
			foreach ($arr as $filterItem) {
				$filterPiece = explode("=", $filterItem);
				if (
					(count($filterPiece) > 1)
					&& ($filterPiece[0] != "")
					&& ($filterPiece[1] != "")
					&& ($filterPiece[0] != "filter")
					&& ($filterPiece[0] != "do")
					&& ($filterPiece[1] != "0")
				) {
					$filter[$filterPiece[0]] = $filterPiece[1];
				}
			}
		}
		unset($filter['DOG_FILTER_EXAM']);	// TODO

		return $filter;
	}

	/**
	 * @param int $id
	 */
	public function actionDefault($id) {
		if ($this->getUser()->isLoggedIn() == false) { // pokud nejsen přihlášen nemám tady co dělat
			$this->flashMessage(DOG_TABLE_DOG_ACTION_NOT_ALLOWED, "alert-info");
			$this->redirect("Homepage:Default");
		}

		$filter = $this->decodeFilterFromQuery();
		$this['dogFilterForm']->setDefaults($filter);

		$page = (empty($id) ? 1 : $id);
		$paginator = new Paginator();
		$paginator->setItemCount($this->dogRepository->getDogsCount($filter, $this->getUser()->getId())); // celkový počet položek
		$paginator->setItemsPerPage(50); // počet položek na stránce
		$paginator->setPage($page); // číslo aktuální stránky, číslováno od 1

		$this->template->paginator = $paginator;
		$this->template->dogs = $this->dogRepository->findDogs($paginator, $filter, $this->getUser()->getId());
		$this->template->dogRepository = $this->dogRepository;
		$this->template->currentLang = $this->langRepository->getCurrentLang($this->session);
		$this->template->enumRepository = $this->enumerationRepository;
		$this->template->filterActivated = (!empty($filter) ? true : false);
	}

	/**
	 * @param Form $form
	 */
	public function dogFilter(Form $form) {
		$filter = "1&";
		foreach ($form->getHttpData() as $key => $value) {
			if ($value != "") {
				$filter .= $key . "=" . $value . "&";
			}
		}
		$this->filter = $filter;
		$this->redirect("default");
	}

	/**
	 * Vytvoří komponentu pro změnu hesla uživatele
	 */
	public function createComponentDogFilterForm() {
		$form = $this->dogFilterForm->create($this->langRepository->getCurrentLang($this->session));
		$form->onSubmit[] = $this->dogFilter;

		$renderer = $form->getRenderer();
		$renderer->wrappers['controls']['container'] = NULL;
		$renderer->wrappers['pair']['container'] = 'div class=form-group';
		$renderer->wrappers['pair']['.error'] = 'has-error';
		$renderer->wrappers['control']['container'] = 'div class=col-md-3';
		$renderer->wrappers['label']['container'] = 'div class="col-md-3 control-label"';
		$renderer->wrappers['control']['description'] = 'span class=help-block';
		$renderer->wrappers['control']['errorcontainer'] = 'span class=help-block';
		//$form->getElementPrototype()->class('form-horizontal');

		return $form;
	}

	/**
	 * Vytvoří komponentu pro změnu hesla uživatele
	 */
	public function createComponentDogForm() {
		$form = $this->dogForm->create($this->langRepository->getCurrentLang($this->session), $this->link("default"));
		$form->onSubmit[] = $this->saveDog;

		$renderer = $form->getRenderer();
		$renderer->wrappers['controls']['container'] = NULL;
		$renderer->wrappers['pair']['container'] = 'div class=form-group';
		$renderer->wrappers['pair']['.error'] = 'has-error';
		$renderer->wrappers['control']['container'] = 'div class=col-md-5';
		$renderer->wrappers['label']['container'] = 'div class="col-md-5 control-label"';
		$renderer->wrappers['control']['description'] = 'span class=help-block';
		$renderer->wrappers['control']['errorcontainer'] = 'span class=help-block';
		$form->getElementPrototype()->class('form-horizontal');

		return $form;
	}

	/**
	 * @param int $id
	 */
	public function actionEdit($id) {
		if ($this->getUser()->isLoggedIn() == false) { // pokud nejsen přihlášen nemám tady co dělat
			$this->flashMessage(DOG_TABLE_DOG_ACTION_NOT_ALLOWED, "alert-info");
			$this->redirect("Homepage:Default");
		}

		if ($id == null) {
			$this->template->currentDog = null;
			$this->template->previousOwners = [];
			$this->template->mIDFound = true;
			$this->template->oIDFound = true;
			$owners[] = $this->getUser()->getId();
			$this['dogForm']['owners']['uID']->setDefaultValue($owners);
		} else {
			$owners = $this->userRepository->findDogOwners($id);	// pokud nejsem majitelem, nemůžu ho editovat
			if (in_array($this->getUser()->getId(), $owners) == false) {
				$this->flashMessage(DOG_FORM_NOT_TRUE_OWNER, "alert-info");
				$this->redirect("default");
			}

			$dog = $this->dogRepository->getDog($id);
			$this->template->mIDFound = ($dog->getMID() == NULL || isset($this['dogForm']['mID']->getItems()[$dog->getMID()]));
			if ($this->template->mIDFound == false) {	// pokud mID psa není v selectu vyjmu ho
				$dog->setMID(0);
			}

			$this->template->oIDFound = ($dog->getOID() == NULL || isset($this['dogForm']['oID']->getItems()[$dog->getOID()]));
			if ($this->template->oIDFound == false) {	// pokud oID psa není v selectu vyjmu ho
				$dog->setOID(0);
			}

			$this->template->currentDog = $dog;
			$this->template->previousOwners = $this->userRepository->findDogPreviousOwners($id);

			$this['dogForm']->setDefaults($dog->extract());
			if ($dog) {
				$this['dogForm']->addHidden('ID', $dog->getID());
			}
			$zdravi = $this->enumerationRepository->findEnumItems($this->langRepository->getCurrentLang($this->session), 14);
			/** @var EnumerationItemEntity $enumEntity */
			foreach ($zdravi as $enumEntity) {
				$dogHealthEntity = $this->dogRepository->getHealthEntityByDogAndType($enumEntity->getOrder(), $id);
				if ($dogHealthEntity != null) {
					$this['dogForm']['dogHealth'][$enumEntity->getOrder()]->setDefaults($dogHealthEntity->extract());
					$this['dogForm']['dogHealth'][$enumEntity->getOrder()]->addHidden('ID', $dogHealthEntity->getID());
				}
			}
			$breeder = $this->userRepository->getBreederByDog($id);
			if ($breeder) {
				$this['dogForm']['breeder']->addHidden("ID", $breeder->getID())->setAttribute("class", "form-control");
				$this['dogForm']['breeder']['uID']->setValue($breeder->getUID());
			}

			$owners = $this->userRepository->findDogOwners($id);
			$this['dogForm']['owners']['uID']->setDefaultValue($owners);
		}
		$this->template->currentLang = $this->langRepository->getCurrentLang($this->session);
		$this->template->dogPics = $this->dogRepository->findDogPics($id);
	}

	/**
	 * Aktualizuje vychozí obrázek u psa
	 */
	public function actionDefaultDogPic() {
		$data = $this->getHttpRequest()->getQuery();
		$dogId = (isset($data['dogId']) ? $data['dogId'] : null);
		$picId = (isset($data['picId']) ? $data['picId'] : null);
		if ($dogId != null && ($picId != null)) {
			$owners = $this->userRepository->findDogOwners($dogId);	// pokud nejsem majitelem, nemůžu ho mazat
			if ($this->getUser()->isLoggedIn() == true && in_array($this->getUser()->getId(), $owners)) {
				$this->dogRepository->setDefaultDogPic($dogId, $picId);
			}
		}
		$this->terminate();
	}

	/**
	 * @param int $id
	 */
	public function actionDelete($id) {
		$owners = $this->userRepository->findDogOwners($id);	// pokud nejsem majitelem, nemůžu ho mazat
		if ($this->getUser()->isLoggedIn() == false || (in_array($this->getUser()->getId(), $owners) == false)) { // pokud nejsen přihlášen nemám tady co dělat
			$this->flashMessage(DOG_FORM_NOT_TRUE_OWNER, "alert-info");
			$this->redirect("default");
		}

		if ($this->dogRepository->delete($id)) {
			$this->flashMessage(DOG_TABLE_DOG_DELETED, "alert-success");
		} else {
			$this->flashMessage(DOG_TABLE_DOG_DELETED_FAILED, "alert-danger");
		}
		$this->redirect("default");
	}

	/**
	 * @param int $id
	 * @param int $pID
	 */
	public function actionDeleteDogPic($id, $pID) {
		$owners = $this->userRepository->findDogOwners($pID);	// pokud nejsem majitelem, nemůžu mazat
		if ($this->getUser()->isLoggedIn() == false || (in_array($this->getUser()->getId(), $owners) == false)) {
			$this->flashMessage(DOG_FORM_NOT_TRUE_OWNER, "alert-info");
			$this->redirect("default");
		} else {
			$this->dogRepository->deleteDogPic($id);
		}
		$this->redirect("edit", $pID);
	}

	public function saveDog(Form $form){
		$supportedFileFormats = ["jpg", "png", "gif"];
		$dogEntity = new DogEntity();
		$pics = [];
		$health = [];
		$breeders = [];
		$owners = [];
		try {
			$formData = $form->getHttpData();
			// zdraví
			foreach($formData['dogHealth'] as $typ => $hodnoty) {
				$healthEntity = new DogHealthEntity();
				$healthEntity->hydrate($hodnoty);
				$healthEntity->setTyp($typ);
				$health[] = $healthEntity;
			}
			unset($formData['dogHealth']);

			/** @var FileUpload $file */
			foreach($formData['pics'] as $file) {
				if ($file != null) {
					$fileController = new FileController();
					if ($fileController->upload($file, $supportedFileFormats, $this->getHttpRequest()->getUrl()->getBaseUrl()) == false) {
						throw new \Exception("Nelze nahrát soubor.");
						break;
					}
					$dogPic = new DogPicEntity();
					$dogPic->setCesta($fileController->getPathDb());
					$pics[] = $dogPic;
				}
			}
			unset($formData['pics']);

			// chovatele
			if (isset($formData['breeder'])) {
				$breederEntity = new BreederEntity();
				$breederEntity->hydrate($formData['breeder']);
				$breeders[] = $breederEntity;
			}
			unset($formData['breeder']);

			// majitel
			if (isset($formData['owners'])) {
				foreach ($formData['owners']['uID'] as $owner) {
					$ownerEntity = new DogOwnerEntity();
					$ownerEntity->setUID($owner);
					$ownerEntity->setSoucasny(true);
					$owners[] = $ownerEntity;
				}
				unset($formData['owners']['uID']);
			}

			$dogEntity->hydrate($formData);

			$this->dogRepository->save($dogEntity, $pics, $health, $breeders, $owners);
			$this->flashMessage(DOG_FORM_ADDED, "alert-success");
			$this->redirect("default");
		} catch (\Exception $e) {
			if ($e instanceof AbortException) {
				throw $e;
			} else {
				$form->addError(DOG_FORM_ADD_FAILED);
				$this->flashMessage(DOG_FORM_ADD_FAILED, "alert-danger");
			}
		}
	}
}