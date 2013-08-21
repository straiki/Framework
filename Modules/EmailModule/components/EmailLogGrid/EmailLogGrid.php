<?php

namespace EmailModule\Components;

use Schmutzka\Application\UI\Module\Grid;


class EmailLogGrid extends Grid
{
	/** @inject @var Schmutzka\Models\EmailLog */
	public $emailLogModel;


	public function build()
	{
		$this->addColumn('custom_email_id', 'Předmět');
		$this->addColumn('to_email', 'Komu');
		$this->addColumn('datetime', 'Odesláno');
		$this->addColumn('type', 'Typ');
	}


	public function dataLoader($grid, array $columns, array $filters, array $order)
	{
		return $this->emailLogModel->fetchAll()
			->order('datetime DESC');
	}

}
