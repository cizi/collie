<?php

namespace App\Forms;

use App\Model\EnumerationRepository;
use Nette;
use Nette\Application\UI\Form;

class LitterApplicationDetailForm extends Nette\Object {

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
		$form = $this->factory->create();
		$form->getElementPrototype()->addAttributes(["onsubmit" => "return requiredFields();"]);

		$barvy = $this->enumerationRepository->findEnumItemsForSelect($currentLang, EnumerationRepository::BARVA);
		$srst = $this->enumerationRepository->findEnumItemsForSelect($currentLang, EnumerationRepository::SRST);
		$plemeno = $this->enumerationRepository->findEnumItemsForSelect($currentLang, EnumerationRepository::PLEMENO);

		$form->addSelect("Plemeno", DOG_FORM_BREED, $plemeno);
		$form->addText("chs", LITTER_APPLICATION_DETAIL_STATION_TITLE, 80);

		// OTEC
		$form->addText("otec", DOG_TABLE_HEADER_FATHER, 40);
		$form->addText("otecDN", DOG_TABLE_HEADER_BIRT, 40);
		$form->addTextArea("otecV", LITTER_APPLICATION_DETAIL_DOG_TITLES, 60, 3);

		$form->addText("otecPP", LITTER_APPLICATION_DETAIL_CARD_NO, 20);
		$form->addSelect("otecBarva", DOG_TABLE_HEADER_COLOR, $barvy);
		$form->addSelect("otecSrst", LITTER_APPLICATION_DETAIL_FUR_TYPE, $srst);
		$form->addText("otecBon", LITTER_APPLICATION_DETAIL_BONITATION, 20);
		$form->addText("otecHeight", DOG_TABLE_HEADER_HEIGHT ,4);

		// matka
		$form->addText("matka", DOG_TABLE_HEADER_MOTHER, 40);
		$form->addText("matkaDN", DOG_TABLE_HEADER_BIRT, 40);
		$form->addTextArea("matkaV", LITTER_APPLICATION_DETAIL_DOG_TITLES, 60, 3);

		$form->addText("matkaPP", LITTER_APPLICATION_DETAIL_CARD_NO, 20);
		$form->addSelect("matkaBarva", DOG_TABLE_HEADER_COLOR, $barvy);
		$form->addSelect("matkaSrst", LITTER_APPLICATION_DETAIL_FUR_TYPE, $srst);
		$form->addText("matkaBon", LITTER_APPLICATION_DETAIL_BONITATION, 20);
		$form->addText("matkaHeight", DOG_TABLE_HEADER_HEIGHT ,4);

		$form->addText("chovatel", LITTER_APPLICATION_DETAIL_BREEDER_ADDRESS, 120);
		$form->addText("datumkryti", MATING_FORM_DATE, 15);
		$form->addText("datumnarozeni", LITTER_APPLICATION_DETAIL_PUPPIES_BIRTHDAY, 15);

		return $form;

		$maleContainer = $form->addContainer('pID');
		$maleContainer->addText("Jmeno", DOG_FORM_NAME_MALE)
			->setAttribute("placeholder", DOG_FORM_NAME);

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