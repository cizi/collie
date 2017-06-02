<?php

namespace App\FrontendModule\Presenters;

use App\Forms\LitterApplicationDetailForm;
use App\Model\EnumerationRepository;
use App\Model\LitterApplicationRepository;
use Nette\Application\AbortException;

class LitterApplicationPdfGeneratorPresenter extends BasePresenter {

	/** @var LitterApplicationRepository */
	private $litterApplicationRepository;

	/** @var  EnumerationRepository */
	private $enumerationRepository;

	/**
	 * LitterApplicationPdfGeneratorPresenter constructor.
	 * @param LitterApplicationRepository $litterApplicationRepository
	 * @param EnumerationRepository $enumerationRepository
	 */
	public function __construct(LitterApplicationRepository $litterApplicationRepository, EnumerationRepository $enumerationRepository) {
		$this->litterApplicationRepository = $litterApplicationRepository;
		$this->enumerationRepository = $enumerationRepository;
	}

	/**
	 * @param int $id
	 * @throws AbortException
	 */
	public function actionDefault($id) {
		$litterApplication = $this->litterApplicationRepository->getLitterApplication($id);
		if ($litterApplication != null) {
			try {
				$latteParams = $litterApplication->getDataDecoded();
				$latteParams['basePath'] = $this->getHttpRequest()->getUrl()->basePath;
				$latteParams['puppiesLines'] = LitterApplicationDetailForm::NUMBER_OF_LINES;
				$latteParams['enumRepository'] = $this->enumerationRepository;
				$latteParams['currentLang'] = $this->langRepository->getCurrentLang($this->session);

				$latte = new \Latte\Engine();
				$latte->setTempDirectory(__DIR__ . '/../../../temp/cache');
				$template = $latte->renderToString(__DIR__ . '/../templates/FeItem2velord17/pdf.latte', $latteParams);

				$pdf = new \Joseki\Application\Responses\PdfResponse($template);
				$pdf->documentTitle = LITTER_APPLICATION . "_" . date("Y-m-d_His");
				$this->sendResponse($pdf);
			} catch (AbortException $e) {
				throw $e;
			} catch (\Exception $e) {
				$this->terminate();
			}
		} else {
			$message = sprintf(LITTER_APPLICATION_DOES_NOT_EXIST, $id);
			$this->flashMessage($message, "alert-danger");
			$this->terminate();
		}
	}
}