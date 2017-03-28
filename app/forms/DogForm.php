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
	public function create($langCurrent) {
		$form = $this->factory->create();
		$form->getElementPrototype()->addAttributes(["onsubmit" => "return requiredFields();"]);

		return $form;
	}

}