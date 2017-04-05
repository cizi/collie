<?php

namespace App\Forms;

use App\Enum\StateEnum;
use App\Model\EnumerationRepository;
use Nette\Application\UI\Form;

class DogFilterForm {

	/** @var FormFactory */
	private $factory;

	/** @var EnumerationRepository */
	private $enumerationRepository;

	/**
	 * @param FormFactory $factory
	 * @param EnumerationRepository $enumerationRepository
	 */
	public function __construct(FormFactory $factory, EnumerationRepository $enumerationRepository) {
		$this->factory = $factory;
		$this->enumerationRepository = $enumerationRepository;
	}

	/**
	 * @return Form
	 */
	public function create($langCurrent) {
		$form = $this->factory->create();
		$form->addGroup(DOG_TABLE_FILTER_LABEL)	;
		//$form->getElementPrototype()->addAttributes(["onsubmit" => "return requiredFields();"]);

		$form->addText("Jmeno", DOG_TABLE_HEADER_NAME)
			->setAttribute("class", "form-control");

		$plemena = $this->enumerationRepository->findEnumItemsForSelect($langCurrent, 7);
		$form->addSelect("Plemeno", DOG_TABLE_HEADER_BREED, $plemena)
			->setAttribute("class", "form-control");

		$barvy = $this->enumerationRepository->findEnumItemsForSelect($langCurrent, 4);
		$form->addSelect("Barva", DOG_TABLE_HEADER_COLOR, $barvy)
			->setAttribute("class", "form-control");

		$pohlavi = $this->enumerationRepository->findEnumItemsForSelectWithEmpty($langCurrent, 8);
		$form->addSelect("Pohlavi", DOG_TABLE_HEADER_SEX, $pohlavi)
			->setAttribute("class", "form-control");

		$form->addSelect("DatNarozeni", DOG_TABLE_HEADER_BIRT)
			->setAttribute("class", "form-control");

		$chovnost = $this->enumerationRepository->findEnumItemsForSelect($langCurrent, 5);
		$form->addSelect("Chovnost", DOG_TABLE_HEADER_BREEDING, $chovnost)
			->setAttribute("class", "form-control");

		$dkk = $this->enumerationRepository->findEnumItemsForSelect($langCurrent, 15);
		$form->addSelect("DOG_FILTER_PROB_DKK", DOG_TABLE_HEADER_PROB_DKK, $dkk)
			->setAttribute("class", "form-control");

		$dlk = $this->enumerationRepository->findEnumItemsForSelect($langCurrent, 16);
		$form->addSelect("DOG_FILTER_PROB_DLK", DOG_TABLE_HEADER_PROB_DLK, $dlk)
			->setAttribute("class", "form-control");

		$zdravi = $this->enumerationRepository->findEnumItemsForSelectWithEmpty($langCurrent, 14);
		$form->addSelect("DOG_FILTER_HEALTH", DOG_TABLE_HEADER_HEALTH, $zdravi)
			->setAttribute("class", "form-control");

		$states = new StateEnum();
		$form->addSelect("DOG_FILTER_LAND",DOG_TABLE_HEADER_LAND, $states->arrayKeyValue())
			->setDefaultValue("CZECH_REPUBLIC")
			->setAttribute("class", "form-control");

		$form->addText("DOG_FILTER_BREEDER",DOG_TABLE_HEADER_BREEDER)
			->setAttribute("class", "form-control");

		$form->addText("DOG_FILTER_EXAM", DOG_TABLE_HEADER_EXAM)
			->setAttribute("class", "form-control");

		$form->addText("Vyska", DOG_TABLE_HEADER_HEIGHT)
		->setAttribute("class", "form-control");

		$form->addGroup();
		$form->addSubmit("filter", DOG_TABLE_BTN_FILTER)
			->setAttribute("class","btn btn-primary margin10");

		return $form;
	}

}