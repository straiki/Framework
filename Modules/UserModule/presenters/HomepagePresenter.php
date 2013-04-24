<?php

namespace UserModule;

class HomepagePresenter extends \AdminModule\BasePresenter
{
	/** @persistent */
	public $id;

	/** @inject @var Schmutzka\Models\User */
	public $userModel;


	/**
	 * Delete
	 * @param int
	 */
	public function handleDelete($id)
	{
		if ($user = $this->userModel->item($id)) {
			if (in_array($user["role"], array("admin", "superadmin"))) {
				$this->flashMessage("Tento uživatelský účet nelze smazat.","error");

			} else {
				$this->userModel->delete($id);
				$this->flashMessage("Záznam byl úspěšně smazán.","success");
			}

		} else {
			$this->flashMessage("Tento záznam neexistuje.", "error");
		}

		$this->redirect("this");
	}


	/**
	 * @param int
	 */
	public function renderEdit($id)
	{
		$this->loadItemHelper($this->userModel, $id);
	}


	/**
	 * User form
	 */
	public function createComponentUserForm()
	{
		$form = $this->context->createUserForm();
		$form->id = $this->id;
		return $form;
	}

}
