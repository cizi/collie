<?php

namespace App\Forms;


use Nette\Application\UI\Form;

class VetForm {

	/** @var FormFactory */
	private $factory;

	/**
	 * @param FormFactory $factory
	 */
	public function __construct(FormFactory $factory) {
		$this->factory = $factory;
	}

	/**
	 * @return Form
	 */
	public function create($linkBack) {
		$form = $this->factory->create();

		$index = 1;
		$form->addText("Jmeno", ENUM_VET_EDIT_NAME)
			->setAttribute("class", "form-control")
			->setAttribute("placeholder", ENUM_VET_EDIT_NAME)
			->setAttribute("tabindex", $index);

		$form->addText("Prijmeni", ENUM_VET_EDIT_SURNAME)
			->setAttribute("class", "form-control")
			->setAttribute("placeholder", ENUM_VET_EDIT_SURNAME)
			->setAttribute("tabindex", $index + 1);

		$form->addText("TitulyPrefix", ENUM_VET_EDIT_PREFIX)
			->setAttribute("class", "form-control")
			->setAttribute("placeholder", ENUM_VET_EDIT_PREFIX)
			->setAttribute("tabindex", $index + 2);

		$form->addText("TitulySuffix", ENUM_VET_EDIT_SUFFIX)
			->setAttribute("class", "form-control")
			->setAttribute("placeholder", ENUM_VET_EDIT_SUFFIX)
			->setAttribute("tabindex", $index + 3);

		$form->addText("Ulice", ENUM_VET_EDIT_STREET)
			->setAttribute("class", "form-control")
			->setAttribute("placeholder", ENUM_VET_EDIT_STREET)
			->setAttribute("tabindex", $index + 4);

		$form->addText("Mesto", ENUM_VET_EDIT_CITY)
			->setAttribute("class", "form-control")
			->setAttribute("placeholder", ENUM_VET_EDIT_CITY)
			->setAttribute("tabindex", $index + 5);

		$form->addText("PSC", ENUM_VET_EDIT_PSC)
			->setAttribute("class", "form-control")
			->setAttribute("placeholder", ENUM_VET_EDIT_PSC)
			->setAttribute("tabindex", $index + 6);

		$form->addButton("back", ENUM_VET_EDIT_BACK)
			->setAttribute("class","btn margin10")
			->setAttribute("onclick", "location.assign('". $linkBack ."')")
			->setAttribute("tabindex", $index + 7);

		$form->addSubmit("confirm", ENUM_VET_EDIT_SAVE)
			->setAttribute("class","btn btn-primary margin10")
			->setAttribute("tabindex", $index + 8);

		return $form;
	}
}