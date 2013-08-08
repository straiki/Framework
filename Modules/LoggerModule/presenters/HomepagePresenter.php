<?php

namespace LoggerModule;

use Nette;
use Nette\Utils\Finder;
use Nette\Utils\Strings;
use Schmutzka\Application\UI\Module\Presenter;
use Schmutzka\Utils\Arrays;


class HomepagePresenter extends Presenter
{

	/**
	 * @param  string
	 */
	public function handleDelete($file)
	{
		if (file_exists($file = $this->paramService->logDir . '/' . $file)) {
			unlink($file);
			$this->flashMessage('Smazáno.', 'success');
			$this->redirect('default');
		}
	}


	/**
	 * @param string
	 */
	public function renderDetail($file)
	{
		$this->template->filename = $file;
		if (file_exists($file = $this->paramService->logDir . '/' . $file)) {
			$this->template->file = $file;

		} else {
			$this->flashMessage('Tento soubor neexistuje.', 'error');
			$this->redirect('default');
		}
	}


	/**
	 * @param  string
	 */
	public function renderIframe($file)
	{
		$this->template->fileContent = file_get_contents($file);
	}


	public function renderDefault()
	{
		$files = Finder::findFiles('*.html')->in($this->paramService->logDir);

		$result = array();
		foreach ($files as $key => $file) {
			$result[] = array(
				'fullname' => $file->getFilename(),
				'name' => Strings::substring($file->getFilename(), 30, 32),
				'filename' => $file->getFilename(),
				'created' => Nette\DateTime::from($file->getMTime()),
			);
		}

		Arrays::sortBySubKeyReverse($result, 'created');

		$this->template->result = $result;
	}

}
