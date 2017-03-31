<?php

namespace App\FrontendModule\Presenters;

use App\Controller\FileController;
use App\Forms\DogFilterForm;
use App\Forms\DogForm;
use App\Model\DogRepository;
use App\Model\Entity\DogEntity;
use App\Model\Entity\DogHealthEntity;
use App\Model\Entity\DogPicEntity;
use App\Model\Entity\EnumerationItemEntity;
use App\Model\EnumerationRepository;
use Nette\Application\AbortException;
use Nette\Forms\Form;
use Nette\Http\FileUpload;

class FeItem2velord11Presenter extends FrontendPresenter {

	/** @var DogRepository */
	private $dogRepository;

	/** @var DogFilterForm */
	private $dogFilterForm;

	/** @var DogForm */
	private $dogForm;

	/** @var EnumerationRepository  */
	private $enumerationRepository;

	public function __construct(DogFilterForm $dogFilterForm, DogForm $dogForm, DogRepository $dogRepository, EnumerationRepository $enumerationRepository) {
		$this->dogFilterForm = $dogFilterForm;
		$this->dogForm = $dogForm;
		$this->dogRepository = $dogRepository;
		$this->enumerationRepository = $enumerationRepository;
	}

	/**
	 * @param int $id
	 */
	public function actionDefault($id) {
		$this->template->dogs = $this->dogRepository->findDogs();
		$this->template->currentLang = $this->langRepository->getCurrentLang($this->session);
		$this->template->enumRepository = $this->enumerationRepository;
	}

	/**
	 * Vytvoøí komponentu pro zmìnu hesla uživatele
	 */
	public function createComponentDogFilterForm() {
		$form = $this->dogFilterForm->create($this->langRepository->getCurrentLang($this->session));
		$form->onSubmit[] = $this->saveDog;

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
	 * Vytvoøí komponentu pro zmìnu hesla uživatele
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
		if ($id == null) {
			$this->template->currentDog = null;
		} else {
			$dog = $this->dogRepository->getDog($id);
			$this->template->currentDog = $dog;
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
		}
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
			$this->dogRepository->setDefaultDogPic($dogId, $picId);
		}
		$this->terminate();
	}

	/**
	 * @param int $id
	 */
	public function actionDelete($id) {
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
		$this->dogRepository->deleteDogPic($id);
		$this->redirect("edit", $pID);
	}

	public function saveDog(Form $form){
		$supportedFileFormats = ["jpg", "png", "gif"];
		$dogEntity = new DogEntity();
		$pics = [];
		$health = [];
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

			$dogEntity->hydrate($formData);
			$this->dogRepository->save($dogEntity, $pics, $health);
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