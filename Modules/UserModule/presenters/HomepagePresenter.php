<?php

namespace UserModule;

use Schmutzka\Application\UI\Module\Presenter;

class HomepagePresenter extends Presenter
{
	/** @inject @var Schmutzka\Models\User */
	public $userModel;


	/**
	 * @param int
	 */
	public function handleDelete($id)
	{
		if ($user = $this->userModel->item($id)) {
			if ($user['role'] == 'admin') {
				$this->flashMessage('Administrátorský účet nelze smazat.', 'error');

			} else {
				$this->userModel->delete($id);
				$this->flashMessage('Záznam byl úspěšně smazán.', 'success');
			}

		} else {
			$this->flashMessage('Tento záznam neexistuje.',  'error');
		}

		$this->redirect('this');
	}

}
