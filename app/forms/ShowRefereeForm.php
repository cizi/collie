<?php

namespace App\Forms;


use App\Model\EnumerationRepository;
use App\Model\RefereeRepository;
use Nette\Forms\Form;

class ShowRefereeForm {

	/** @var FormFactory */
	private $factory;

	/** @var EnumerationRepository  */
	private $enumRepository;

	/** @var  RefereeRepository */
	private $refereeRepository;

	/**
	 * @param FormFactory $factory
	 * @param EnumerationRepository $enumerationRepository
	 * @param RefereeRepository $refereeRepository
	 */
	public function __construct(FormFactory $factory, EnumerationRepository $enumerationRepository, RefereeRepository $refereeRepository) {
		$this->factory = $factory;
		$this->enumRepository = $enumerationRepository;
		$this->refereeRepository = $refereeRepository;
	}

	/**
	 * @return Form
	 */
	public function create($linkBack, $lang) {
		$form = $this->factory->create();
		$form->getElementPrototype()->addAttributes(["onsubmit" => "return requiredFields();"]);

		$index = 0;
		$form->addHidden("vID");

		$referees = $this->refereeRepository->findRefereesForSelect();
		$form->addSelect("rID", SHOW_REFEREE_FORM_REFEREE, $referees)
			->setAttribute("class", "form-control")
			->setAttribute("placeholder", SHOW_TABLE_REFEREE)
			->setAttribute("tabindex", $index + 1);

		$classes = $this->enumRepository->findEnumItemsForSelect($lang, 20);
		$form->addSelect("Trida", SHOW_REFEREE_FORM_CLASS, $classes)
			->setAttribute("class", "form-control")
			->setAttribute("placeholder", SHOW_TABLE_DATE)
			->setAttribute("tabindex", $index + 2);

		$plemeno = $this->enumRepository->findEnumItemsForSelect($lang, 7);
		$form->addSelect("Plemeno", SHOW_REFEREE_FORM_BREED, $plemeno)
			->setAttribute("class", "form-control")
			->setAttribute("tabindex", $index + 3);

		$form->addButton("back", VET_EDIT_BACK)
			->setAttribute("class","btn margin10")
			->setAttribute("onclick", "location.assign('". $linkBack ."')")
			->setAttribute("tabindex", $index + 7);

		$form->addSubmit("confirm", VET_EDIT_SAVE)
			->setAttribute("class","btn btn-primary margin10")
			->setAttribute("tabindex", $index + 8);

		return $form;
	}

}