<?php

namespace App\Forms;

use Nette;
use Nette\Application\UI\Form;

class ContactForm {

    use Nette\SmartObject;

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
	public function create() {
		$form = $this->factory->create();
		// $form->getElementPrototype()->addAttributes(["onsubmit" => "return requiredFields();"]);

		$form->addText("name")
			//->setAttribute("tabindex", "1")
			->setAttribute("class", "form-control contactForm")
			->setAttribute("placeholder", CONTACT_FORM_NAME);

		$form->addText("contactEmail")
			->setAttribute("type","email")
			//->setAttribute("tabindex", "2")
			->setAttribute("class", "form-control contactForm")
			->setAttribute("placeholder", CONTACT_FORM_EMAIL);

		$form->addText("subject")
			//->setAttribute("tabindex", "3")
			->setAttribute("class", "form-control contactForm")
			->setAttribute("placeholder", CONTACT_FORM_SUBJECT);

		$form->addUpload("attachment")
			//->setAttribute("tabindex", "4")
			->setAttribute("placeholder", CONTACT_FORM_ATTACHMENT)
			->setAttribute("class", "form-control contactForm");

		$form->addTextArea("text", null, null, 7)
			//->setAttribute("tabindex", "5")
			->setAttribute("placeholder", CONTACT_FORM_TEXT)
			->setAttribute("class", "form-control contactForm")
			->setAttribute("style", "margin-top: 5px; margin-left: 5px;");

		$form->addHidden("backlink");

		$form->addSubmit("confirmContactForm", CONTACT_FORM_BUTTON_CONFIRM)
			//->setAttribute("tabindex", "6")
			->setAttribute("class","btn btn-success");

		return $form;
	}
}