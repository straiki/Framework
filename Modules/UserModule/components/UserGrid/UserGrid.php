<?php

namespace UserModule\Components;

use Schmutzka\Application\UI\Module\Grid;


class UserGrid extends Grid
{
	/** @inject @var Schmutzka\Models\User */
	public $userModel;


	public function build()
	{
		$this->setPrimaryKey('id');
		$this->addColumn('email', 'Email');
		$this->addColumn('login', 'Jméno');
		$this->addColumn('created', 'Registrován');
		$this->addColumn('last_active', 'Poslední aktivita');
		$this->addColumn('role', 'Role');
		$this->addTemplateValue('roles', (array) $this->moduleParams->roles);

		$this->addEditRowAction();
		$this->addDeleteRowAction();
	}

}
