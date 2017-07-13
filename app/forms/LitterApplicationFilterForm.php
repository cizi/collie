<?php

namespace App\Forms;

use App\Model\DogRepository;
use App\Model\EnumerationRepository;
use Nette;
use Nette\Application\UI\Form;

class LitterApplicationFilterForm extends Nette\Object {

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
	 * @param string $currentLang
	 * @return Form
	 */
	public function create($currentLang, $isAdmin = false) {
		$counter = 1;
		$form = $this->factory->create();

		$breeds = $this->enumerationRepository->findEnumItemsForSelect($currentLang, EnumerationRepository::PLEMENO);
		$form->addSelect("Plemeno", DOG_FORM_BREED, $breeds)
			->setAttribute("class", "form-control")
			->setAttribute("tabindex", $counter + 1);

		$form->addText("DatumNarozeni", DOG_FORM_BIRT)
			->setAttribute("class", "form-control")
			->setAttribute("tabindex", $counter + 2);

		if ($isAdmin) {
			$rewritten = [1 => LITTER_APPLICATION_SAVE_UNREWRITTEN, 2 => LITTER_APPLICATION_SAVE_REWRITTEN, 3 =>LITTER_APPLICATION_SAVE_ALLREWRITTEN];
			$form->addSelect("Zavedeno", LITTER_APPLICATION_SAVE_REWRITTEN, $rewritten)
				->setAttribute("class", "form-control")
				->setDefaultValue(3)
				->setAttribute("tabindex", $counter + 2);
		}

		$form->addSubmit("filter", DOG_TABLE_BTN_FILTER)
			->setAttribute("class", "btn btn-primary marginMinus12")
			->setAttribute("tabindex", $counter + 4);

		return $form;
	}
}