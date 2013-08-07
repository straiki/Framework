<?php

namespace UserModule\Components;

use Schmutzka\Application\UI\Module\Grid;


class UserGrid extends Grid
{
	/** @inject @var Schmutzka\Models\User */
	public $userModel;


	public function build()
	{
		$params = $this->getModuleParams();
		$this->template->roles = $params->roles;

		$this->setPrimaryKey('id');
		$this->addColumn('email', 'Email');
		$this->addColumn('login', 'Jméno', '20%');
		$this->addColumn('created', 'Registrován', '10%');
		$this->addColumn('last_active', 'Aktivita', '10%');
		$this->addColumn('role', 'Role', '12%');

		$this->addEditRowAction();
		$this->addDeleteRowAction();
	}

}
