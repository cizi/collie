<?php

namespace App\Forms;

use App\Model\EnumerationRepository;
use Nette\Application\UI\Form;

class DogForm {

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
	public function create($langCurrent, $linkBack) {
		$form = $this->factory->create();
		$form->getElementPrototype()->addAttributes(["onsubmit" => "return requiredFields();"]);

		$form->addText("TitulyPredJmenem", DOG_FORM_NAME_PREFIX)
			->setAttribute("class", "form-control")
			->setAttribute("placeholder", DOG_FORM_NAME_PREFIX);

		$form->addText("Jmeno", DOG_FORM_NAME)
			->setAttribute("class", "form-control tinym_required_field")
			->setAttribute("placeholder", DOG_FORM_NAME);

		$form->addText("TitulyZaJmenem", DOG_FORM_NAME_SUFFIX)
			->setAttribute("class", "form-control")
			->setAttribute("placeholder", DOG_FORM_NAME_SUFFIX);

		$plemeno = $this->enumerationRepository->findEnumItemsForSelect($langCurrent, 7);
		$form->addSelect("Plemeno", DOG_FORM_BREED, $plemeno)
			->setAttribute("class", "form-control");

		$srst = $this->enumerationRepository->findEnumItemsForSelect($langCurrent, 11);
		$form->addSelect("Srst", DOG_FORM_FUR, $srst)
			->setAttribute("class", "form-control");

		$barvy = $this->enumerationRepository->findEnumItemsForSelect($langCurrent, 4);
		$form->addSelect("Barva", "", $barvy)
			->setAttribute("class", "form-control");

		$form->addText("BarvaKomentar", DOG_FORM_FUR_COM)
			->setAttribute("class", "form-control")
			->setAttribute("placeholder", DOG_FORM_FUR_COM);

		$pohlavi = $this->enumerationRepository->findEnumItemsForSelect($langCurrent, 8);
		$form->addSelect("Pohlavi", DOG_FORM_SEX, $pohlavi)
			->setAttribute("class", "form-control");

		$form->addText("DatNarozeni", DOG_FORM_BIRT)
			->setAttribute("class", "form-control")
			->setAttribute("placeholder", DOG_FORM_BIRT);

		$form->addText("Vyska", DOG_FORM_HEIGHT)
			->setAttribute("class", "form-control")
			->setAttribute("placeholder", DOG_FORM_HEIGHT);

		$form->addText("Vaha", DOG_FORM_WEIGHT)
			->setAttribute("class", "form-control")
			->setAttribute("placeholder", DOG_FORM_WEIGHT);

		$form->addText("DatUmrti", DOG_FORM_WEIGHT)
			->setAttribute("class", "form-control")
			->setAttribute("placeholder", DOG_FORM_WEIGHT);

		$form->addText("UmrtiKomentar", DOG_FORM_WEIGHT)
			->setAttribute("class", "form-control")
			->setAttribute("placeholder", DOG_FORM_WEIGHT);

		$form->addText("CisloZapisu", DOG_FORM_NO_OF_REC)
			->setAttribute("class", "form-control")
			->setAttribute("placeholder", DOG_FORM_NO_OF_REC);

		$form->addText("PCisloZapisu", DOG_FORM_NO_OF_REC2)
			->setAttribute("class", "form-control")
			->setAttribute("placeholder", DOG_FORM_NO_OF_REC2);

		$form->addText("Tetovani", DOG_FORM_NO_OF_TATTOO)
			->setAttribute("class", "form-control")
			->setAttribute("placeholder", DOG_FORM_NO_OF_TATTOO);

		$form->addText("Cip", DOG_FORM_NO_OF_CHIP)
			->setAttribute("class", "form-control")
			->setAttribute("placeholder", DOG_FORM_NO_OF_CHIP);

		$form->addText("Bonitace", DOG_FORM_BON)
			->setAttribute("class", "form-control")
			->setAttribute("placeholder", DOG_FORM_BON);

		$chovnost = $this->enumerationRepository->findEnumItemsForSelect($langCurrent, 5);
		$form->addSelect("Chovnost", DOG_FORM_BREEDING, $chovnost)
			->setAttribute("class", "form-control");

		$form->addText("ChovnyKomentar", DOG_FORM_BREEDING_COM)
			->setAttribute("class", "form-control")
			->setAttribute("placeholder", DOG_FORM_BREEDING_COM);

		$form->addText("ZdravotniKomentar", DOG_FORM_HEALTH_COM)
			->setAttribute("class", "form-control")
			->setAttribute("placeholder", DOG_FORM_HEALTH_COM);

		// vyšetøení a zdraví TODO


		$varalate = $this->enumerationRepository->findEnumItemsForSelect($langCurrent, 12);
		$form->addSelect("Varlata", DOG_FORM_BOLOCKS, $varalate)
			->setAttribute("class", "form-control");

		$skus = $this->enumerationRepository->findEnumItemsForSelect($langCurrent, 10);
		$form->addSelect("Skus", DOG_FORM_CHEW, $skus)
			->setAttribute("class", "form-control");

		$form->addText("ZubyKomentar", DOG_FORM_TEETH_COM)
			->setAttribute("class", "form-control")
			->setAttribute("placeholder", DOG_FORM_TEETH_COM);

		// chovatele

		// majitele

		// puvdno majitel

		$form->addMultiUpload("pics", DOG_FORM_PIC_UPLOAD)
			->setAttribute("class", "form-control");

		$form->addButton("back", VET_EDIT_BACK)
			->setAttribute("class", "btn margin10")
			->setAttribute("onclick", "location.assign('" . $linkBack . "')");

		$form->addSubmit("confirm", VET_EDIT_SAVE)
			->setAttribute("class", "btn btn-primary margin10");

		return $form;
	}
}