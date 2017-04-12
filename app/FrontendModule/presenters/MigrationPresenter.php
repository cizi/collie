<?php

namespace App\FrontendModule\Presenters;

use App\Model\DogRepository;
use App\Model\Entity\DogPicEntity;
use App\Model\UserRepository;
use Nette\Http\FileUpload;
use Nette\Utils\Finder;

class MigrationPresenter extends BasePresenter	 {

	/** @var DogRepository */
	private $dogRepository;

	/** @var UserRepository  */
	private$userRepository;

	public function __construct(DogRepository $dogRepository, UserRepository $userRepository) {
		$this->dogRepository = $dogRepository;
		$this->userRepository = $userRepository;
	}

	/**
	 * Migrace obrázku
	 * volání www/migration/pic-migration
	 * @throws \Nette\Application\AbortException
	 */
	public function actionPicMigration() {
		$pocet = 0;
		/**
		 * @var  $key
		 * @var \SplFileInfo $file
		 */
		foreach (Finder::findFiles('*.jpg')->in('./!migrace/genPhoto') as $key => $file) {
			try {
				// $key; // $key je řetězec s názvem souboru včetně cesty
				$dogPicEntity = new DogPicEntity();
				if (strpos($file->getFilename(), 'Main') !== false) {
					$dogPicEntity->setVychozi(true);
				} else {
					$dogPicEntity->setVychozi(false);
				}

				$baseUrl = $this->getHttpRequest()->getUrl()->getBaseUrl();
				$pathDb = $baseUrl . 'upload/' . date("Ymd-His") . "-" . $file->getFilename();    // cesta do DB
				$path = UPLOAD_PATH . '/' . date("Ymd-His") . "-" . $file->getFilename();    // sem fyzicky nahrávám
				copy($file->getRealPath(), $path);

				$dogPicEntity->setCesta($pathDb);
				preg_match_all('!\d+!', $file->getFilename(), $matches);
				$dogPicEntity->setPID((int)implode('', $matches[0]));
				$this->dogRepository->saveDogPic($dogPicEntity);
				$pocet++;
			} catch (\Exception $e) {
				echo "Soubor {$key} nelze nahraát z důvodu: " . $e->getMessage() . "<br />";
			}
		}
		echo "Zpracováno obrázků: " . $pocet;
		$this->terminate();
	}

	/**
	 * Migrace uživatelů
	 */
	public function actionUserMigration() {
		$migrationResult = $this->userRepository->migrateUserFromOldStructure();
		file_put_contents('user_migration_log.txt', print_r($migrationResult, true));
		dump($migrationResult);
		$this->terminate();
	}
}