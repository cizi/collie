<?php

namespace App\Forms;

use App\Model\EnumerationRepository;
use Nette;
use Nette\Application\UI\Form;

class MatingListDetailForm extends Nette\Object {

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
	 * @param array $languages
	 * @param int $level
	 * @return Form
	 */
	public function create($currentLang, $linkBack) {
		$counter = 1;
		$form = $this->factory->create();
		$form->getElementPrototype()->addAttributes(["onsubmit" => "return requiredFields();"]);
		$barvy = $this->enumerationRepository->findEnumItemsForSelect($currentLang, 4);

		$plemeno = $this->enumerationRepository->findEnumItemsForSelect($currentLang, 7);
		$form->addHidden('cID');

		$form->addSelect("Plemeno", DOG_FORM_BREED, $plemeno)
			->setAttribute("tabindex", $counter);

		$maleContainer = $form->addContainer('pID');
		$maleContainer->addText("Jmeno", DOG_FORM_NAME_MALE)
			->setAttribute("placeholder", DOG_FORM_NAME)
			->setAttribute("tabindex", $counter + 1);

		$maleContainer->addText("CisloZapisu", DOG_FORM_NO_OF_REC)
			->setAttribute("placeholder", DOG_FORM_NO_OF_REC)
			->setAttribute("tabindex", $counter + 2);

		$maleContainer->addText("Cip", DOG_FORM_NO_OF_CHIP)
			->setAttribute("placeholder", DOG_FORM_NO_OF_CHIP)
			->setAttribute("tabindex", $counter + 3);

		$maleContainer->addText("DatumNarozeni", DOG_FORM_BIRT)
			->setAttribute("placeholder", DOG_FORM_BIRT)
			->setAttribute("tabindex", $counter + 4);

		$maleContainer->addSelect("Barva", DOG_FORM_FUR_COLOUR, $barvy)
			->setAttribute("placeholder", DOG_FORM_FUR_COLOUR)
			->setAttribute("tabindex", $counter + 5);

		$maleContainer->addText("Vyska", DOG_FORM_HEIGHT)
			->setAttribute("placeholder", DOG_FORM_HEIGHT)
			->setAttribute("tabindex", $counter + 6);

		$maleContainer->addText("Bonitace", DOG_FORM_BON_DATE)
			->setAttribute("placeholder", DOG_FORM_BON_DATE)
			->setAttribute("tabindex", $counter + 7);

		$maleContainer->addText("Misto", MATING_FORM_PLACE2)
			->setAttribute("placeholder", MATING_FORM_PLACE2)
			->setAttribute("tabindex", $counter + 8);

		// --------------------------------------------------------

		$femaleContainer = $form->addContainer('fID');
		$femaleContainer->addText("Jmeno", DOG_FORM_NAME_FEMALE)
			->setAttribute("placeholder", DOG_FORM_NAME)
			->setAttribute("tabindex", $counter + 9);

		$femaleContainer->addText("CisloZapisu", DOG_FORM_NO_OF_REC)
			->setAttribute("placeholder", DOG_FORM_NO_OF_REC)
			->setAttribute("tabindex", $counter + 10);

		$femaleContainer->addText("Cip", DOG_FORM_NO_OF_CHIP)
			->setAttribute("placeholder", DOG_FORM_NO_OF_CHIP)
			->setAttribute("tabindex", $counter + 11);

		$femaleContainer->addText("DatumNarozeni", DOG_FORM_BIRT)
			->setAttribute("placeholder", DOG_FORM_BIRT)
			->setAttribute("tabindex", $counter + 12);

		$femaleContainer->addSelect("Barva", DOG_FORM_FUR_COLOUR, $barvy)
			->setAttribute("placeholder", $barvy)
			->setAttribute("tabindex", $counter + 13);

		$femaleContainer->addText("Vyska", DOG_FORM_HEIGHT)
			->setAttribute("placeholder", DOG_FORM_HEIGHT)
			->setAttribute("tabindex", $counter + 14);

		$femaleContainer->addText("Bonitace", DOG_FORM_BON_DATE)
			->setAttribute("placeholder", DOG_FORM_BON_DATE)
			->setAttribute("tabindex", $counter + 15);

		$femaleContainer->addText("Misto", MATING_FORM_PLACE2)
			->setAttribute("placeholder", MATING_FORM_PLACE2)
			->setAttribute("tabindex", $counter + 16);

		// ------------------------------------------------------------

		$form->addText("DatumKryti", MATING_FORM_DATE)
			->setAttribute("placeholder", MATING_FORM_DATE)
			->setAttribute("tabindex", $counter + 17);

		$form->addText("MistoKryti", MATING_FORM_PLACE)
			->setAttribute("placeholder", MATING_FORM_PLACE)
			->setAttribute("tabindex", $counter + 18);

		$form->addText("Inseminace", MATING_FORM_INSEMINATION)
			->setAttribute("placeholder", MATING_FORM_INSEMINATION)
			->setAttribute("tabindex", $counter + 19);

		$form->addTextArea("Dohoda", MATING_FORM_AGREEMENT, 100, 10)
			->setAttribute("placeholder", MATING_FORM_AGREEMENT)
			->setAttribute("tabindex", $counter + 20);

		$form->addTextArea("MajitelPsa", MATING_FORM_MALE_OWNER, 100, 10)
			->setAttribute("placeholder", MATING_FORM_MALE_OWNER)
			->setAttribute("tabindex", $counter + 21);

		$form->addTextArea("MajitelFeny", MATING_FORM_FEMALE_OWNER, 100, 10)
			->setAttribute("placeholder", MATING_FORM_FEMALE_OWNER)
			->setAttribute("tabindex", $counter + 22);

		$form->addButton("back", MATING_FORM_OVERAGAIN)
			->setAttribute("class", "btn margin10")
			->setAttribute("onclick", "location.assign('" . $linkBack . "')")
			->setAttribute("tabindex", $counter + 24);

		$form->addSubmit("generate", MATING_FORM_GENERATE)
			->setAttribute("class", "btn btn-primary margin10")
			->setAttribute("tabindex", $counter + 23);

		return $form;
	}
}