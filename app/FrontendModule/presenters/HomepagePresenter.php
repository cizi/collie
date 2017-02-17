<?php

namespace App\FrontendModule\Presenters;

use App\Controller\FileController;
use App\Controller\MenuController;
use App\Forms\ContactForm;
use App\Model\BlockRepository;
use App\Model\Entity\BlockContentEntity;
use App\Model\Entity\MenuEntity;
use App\Model\MenuRepository;
use Nette;
use App\Enum\WebWidthEnum;
use App\Model\SliderPicRepository;
use App\Model\WebconfigRepository;
use App\FrontendModule\Presenters;
use Nette\Http\FileUpload;

class HomepagePresenter extends BasePresenter {

	/** var SliderPicRepository */
	private $sliderPicRepository;

	/** @var ContactForm */
	private $contactForm;

	/** @var MenuController */
	private $menuController;

	/** @var FileController */
	private $fileController;

	public function __construct(
		SliderPicRepository $sliderPicRepository,
		ContactForm $contactForm,
		MenuController $menuController,
		FileController $fileController
	) {
		$this->sliderPicRepository = $sliderPicRepository;
		$this->contactForm = $contactForm;
		$this->menuController = $menuController;
		$this->fileController = $fileController;
	}

	/**
	 * @param string $lang
	 * @param string $id
	 */
	public function renderDefault($lang, $id) {
		if (empty($lang)) {
			$lang = $this->langRepository->getCurrentLang($this->session);
			$this->redirect("default", [ 'lang' => $lang, 'id' => $id]);
		}

		$userBlocks = [];
		$availableLangs = $this->langRepository->findLanguages();
		// what if link will have the same shortcut like language
		if (isset($availableLangs[$lang]) && ($lang != $this->langRepository->getCurrentLang($this->session))) {
			$this->langRepository->switchToLanguage($this->session, $lang);
			$this->redirect("default", [ 'lang' => $lang, 'id' => $id ]);
		} else {
			if ((empty($id) || ($id == "")) && !empty($lang) && (!isset($availableLangs[$lang]))) {
				$id = $lang;
			}
			if (empty($id) || ($id == "")) {    // try to find default
				$userBlocks[] = $this->getDefaultBlock();
			} else {
				$userBlocks = $this->blockRepository->findAddedBlockFronted($id,
					$this->langRepository->getCurrentLang($this->session));
				if (empty($userBlocks)) {
					$userBlocks[] = $this->getDefaultBlock();
				}
			}
			// because of sitemap.xml
			$allWebLinks = $this->menuRepository->findAllItems();
			$this->template->webAvailebleLangs = $availableLangs;
			$this->template->availableLinks = $allWebLinks;
			/** @var MenuEntity $menuLink */
			foreach($allWebLinks as $menuLink) {
				if ($menuLink->getLink() == $id) {
					$this->template->currentLink = $menuLink;
				}
}			}

			$this->template->userBlocks = $userBlocks;
	}

	/**
	 * Proceed contact form
	 *
	 * @param Nette\Application\UI\Form $form
	 * @param $values
	 * @throws \Exception
	 * @throws \phpmailerException
	 */
	public function contactFormSubmitted($form, $values) {
		if (
			isset($values['contactEmail']) && $values['contactEmail'] != ""
			&& isset($values['name']) && $values['name'] != ""
			&& isset($values['subject']) && $values['subject'] != ""
			&& isset($values['text']) && $values['text'] != ""
		) {
			$supportedFilesFormat = ["png", "jpg", "bmp", "pdf", "doc", "xls", "docx", "xlsx"];
			$fileError = false;
			$path = "";
			if (!empty($values['attachment'])) {
				/** @var FileUpload $file */
				$file = $values['attachment'];
				if (!empty($file->name)) {
					$fileController = new FileController();
					if ($fileController->upload($file, $supportedFilesFormat, $this->getHttpRequest()->getUrl()->getBaseUrl()) == false) {
						$fileError = true;
						$this->flashMessage(CONTACT_FORM_UNSUPPORTED_FILE_FORMAT, "alert-danger");
					} else {
						$path = $fileController->getPath();
					}
				}
			}

			if ($fileError == false) {
				$email = new \PHPMailer();
				$email->CharSet = "UTF-8";
				$email->From = $values['contactEmail'];
				$email->FromName = $values['name'];
				$email->Subject = CONTACT_FORM_EMAIL_MY_SUBJECT . " - " . $values['subject'];
				$email->Body = $values['text'];
				$email->AddAddress($this->webconfigRepository->getByKey(WebconfigRepository::KEY_CONTACT_FORM_RECIPIENT, WebconfigRepository::KEY_LANG_FOR_COMMON));
				if (!empty($path)) {
					$email->AddAttachment($path);
				}
				$email->Send();
				$this->flashMessage(CONTACT_FORM_WAS_SENT, "alert-success");
			}
		} else {
			$this->flashMessage(CONTACT_FORM_SENT_FAILED, "alert-danger");
		}
		$this->redirect("default");
	}

	public function createComponentContactForm() {
		$form = $this->contactForm->create();
		if ($this->webconfigRepository->getByKey(WebconfigRepository::KEY_CONTACT_FORM_RECIPIENT, WebconfigRepository::KEY_LANG_FOR_COMMON) == "") {
			$form["confirm"]->setDisabled();
		}
		$form->onSuccess[] = $this->contactFormSubmitted;
		return $form;
	}

	/**
	 * returns default block
	 *
	 * @return BlockContentEntity|\App\Model\Entity\BlockEntity
	 */
	private function getDefaultBlock() {
		$id = $this->webconfigRepository->getByKey(WebconfigRepository::KEY_WEB_HOME_BLOCK,
			WebconfigRepository::KEY_LANG_FOR_COMMON);

		$blockContentEntity = new BlockContentEntity();
		if (!empty($id)) {
			$blockContentEntity = $this->blockRepository->getBlockById($this->langRepository->getCurrentLang($this->session), $id);
		}

		return $blockContentEntity;
	}
}
