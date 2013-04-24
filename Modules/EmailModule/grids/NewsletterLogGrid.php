<?php

namespace EmailModule\Grids;

use NiftyGrid;
use Schmutzka\Forms\Form;
use Models;

class NewsletterLogGrid extends NiftyGrid\Grid
{
	/** @var Models/NewsletterLog */
    protected $newsletterLogModel;

	/** @var Models/User */
    private $userModel;


	/**
	 * @param Models\NewsletterLog
	 * @param Models\User
	 */
    public function __construct(Models\NewsletterLog $newsletterLogModel, Models\User $userModel)
    {
        parent::__construct();
        $this->newsletterLogModel = $newsletterLogModel;
        $this->userModel = $userModel;
    }


	/**
	 * Configure
	 * @param presenter
	 */
    protected function configure($presenter)
    {
		$result = $this->newsletterLogModel->all()->order("sent DESC");
        $source = new NiftyGrid\DataSource($result);
        $this->setDataSource($source);

		// grid structure
		$this->addColumn("sent", "Odesláno", "15%")->setDateRenderer();
		$this->addColumn("name", "Interní označení", "15%");
		$this->addColumn("subject", "Předmět", "15%", 300);
		$userList = $this->userModel->fetchPairs("id", "name"); // 3DO: better name?
		// $this->addColumn("user_id", "Odeslal", "12%")->setListRenderer($userList);
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
		// $this->addColumn("content", "Obsah:", NULL, 400);
    }

}