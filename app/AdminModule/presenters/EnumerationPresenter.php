<?php

namespace App\AdminModule\Presenters;


use App\Forms\EnumerationForm;
use App\Forms\EnumerationItemForm;
use App\Model\Entity\EnumerationItemEntity;
use App\Model\EnumerationRepository;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;

class EnumerationPresenter extends SignPresenter {

	/** @var EnumerationRepository */
	private $enumerationRepository;

	/** @var  EnumerationForm */
	private $enumerationForm;

	/** @var  EnumerationItemForm */
	private $enumerationItemForm;

	public function __construct(
		EnumerationRepository $enumerationRepository,
		EnumerationForm $enumerationForm,
		EnumerationItemForm $enumerationItemForm
	) {
		$this->enumerationRepository = $enumerationRepository;
		$this->enumerationForm = $enumerationForm;
		$this->enumerationItemForm = $enumerationItemForm;
	}

	public function actionDefault() {
		$this->template->enums = $this->enumerationRepository->findEnums($this->langRepository->getCurrentLang($this->session));
	}

	public function actionDelete($id) {
		$this->enumerationRepository->deleteEnum($id);
		$this->redirect("default");
	}

	/**
	 * Smaže položku èíselníku
	 * @param int $headerId
	 * @param int $order
	 */
	public function actionDeleteItem($headerId, $order) {
		$this->enumerationRepository->deleteEnumItem($headerId, $order);
		$this->redirect("edit", $headerId);
	}

	public function actionEditItem($order, $getEnumHeaderId) {
		$items = $this->enumerationRepository->findEnumItemsByOrder($order);
		$data = [];
		/** @var EnumerationItemEntity $item */
		foreach ($items as $item) {
			$data[$item->getLang()]['item'] = $item->getItem();
			$data[$item->getLang()]["order"] = $item->getOrder();
			$data[$item->getLang()]["enum_header_id"] = $item->getEnumHeaderId();
			$data[$item->getLang()]["id"] = $item->getId();
		}
		if (count($items) == 0) {
			foreach($this->langRepository->findLanguages() as $lang) {
				$data[$lang]["enum_header_id"] = $getEnumHeaderId;
			}
		}
		$this['enumerationItemForm']->setDefaults($data);
	}

	/**
	 * @param int $id
	 */
	public function actionEdit($id) {
		$this->template->enumHeaderId = $id;
		$this->template->langs = $this->langRepository->findLanguages();
		$this->template->enumerationRepository = $this->enumerationRepository;
		if ($id != null) {
			foreach($this->langRepository->findLanguages() as $lang) {
				$desc = $this->enumerationRepository->getEnumDescription($id, $lang);
				$data[$lang] = [ 'description' => $desc->getDescription() ];
				$this['enumerationForm']->setDefaults($data);
			}
			$this->template->enumItems = $this->enumerationRepository->findEnumItems($this->langRepository->getCurrentLang($this->session), $id);
		} else {
			$this->template->enumItems = [];
		}
	}

	public function createComponentEnumerationForm() {
		$form = $this->enumerationForm->create($this->langRepository->findLanguages(), $this->link("default"));
		$form->onSuccess[] = $this->saveEdit;
		return $form;
	}

	public function createComponentEnumerationItemForm() {
		$form = $this->enumerationItemForm->create($this->langRepository->findLanguages(), $this->link("default"));
		$form->onSuccess[] = $this->saveEditItem;

		return $form;
	}

	public function saveEdit(Form $form, ArrayHash $values) {
		dump($values);
	}

	/**
	 * Uloží položku èíselníku
	 * @param Form $form
	 * @param ArrayHash $values
	 */
	public function saveEditItem(Form $form, ArrayHash $values) {
		$items = [];
		foreach ($values as $data) {
			if (is_a($data, "Nette\Utils\ArrayHash")) {
				$item = new EnumerationItemEntity();
				$item->setEnumHeaderId($data["enum_header_id"]);
				$item->setOrder($data["order"]);
				$item->setItem($data["item"]);
				$item->setLang($data["lang"]);
				if (isset($data["id"]) && ($data["id"] != "")) {
					$item->setId($data["id"]);
				}
				$items[] = $item;
			}
		}
		if ($this->enumerationRepository->saveEnumerationItems($items)) {
			$this->flashMessage(ENUM_EDIT_ITEM_SAVE, "alert-success");
			$this->redirect("edit", reset($items)->getEnumHeaderId());
		} else {
			$this->flashMessage(ENUM_EDIT_ITEM_FAIL, "alert-danger");
		}
	}
	
}