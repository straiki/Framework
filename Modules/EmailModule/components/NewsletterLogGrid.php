<?php

namespace EmailModule\Grids;

use NiftyGrid;
use Schmutzka\Forms\Form;
use Schmutzka\Application\UI\Module\Grid;

class NewsletterLogGrid extends Grid
{
	/** @inject @var Models/NewsletterLog */
	public $newsletterLogModel;


	/**
	 * @param presenter
	 */
	protected function configure($presenter)
	{
		$result = $this->newsletterLogModel->fetchAll()->order("sent DESC");
		$source = new NiftyGrid\DataSource($result);
		$this->setDataSource($source);
		$this->setModel($this->newsletterLogModel);

		$this->addColumn("sent", "Odesláno", "15%")
			->setDateRenderer();
		$this->addColumn("name", "Interní označení", "15%");
		$this->addColumn("subject", "Předmět", "15%", 300);

		$userList = $this->userModel->fetchPairs("id", "name");
		$this->addColumn("user_id", "Odeslal", "12%")
			->setListRenderer($userList);

		$this->addColumn("type", "Typ", "15%")->setRenderer(function($row) {
			if ($row->type == "spec_mail") {
				return "Konkrétní adresy";

			} else {
				return "Skupině uživatelů " . $row->user_group;
			}
		});

		$this->addColumn("email_list", "Odešlo na emaily")->setRenderer(function($row) {
			$emailList = unserialize($row->email_list);
			$return = NULL;
			foreach ($emailList as $email) {
				$return = $email . ", ";
			}
			$return = rtrim($return, ", ");
			return $return;
		});
	}

}
