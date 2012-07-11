<?php

namespace Components;

use Nette\Utils\Html;

class FavourControl extends \Schmutzka\Application\UI\Control
{

	/** @var string */
	public $favoriteClass = "favorite";

	/** @var string */
	public $nonFavoriteClass = "nonFavorite";

	/** @var bool */	
	public $useAjax = TRUE;


	/** @var \Model */
	private $model;

	/** @var int */
	private $userId;

	/** @var string */
	private $tableKey;

	/** @var session */
	private $searched;


	public function __construct($model, $userId, $tableKey, $searched = NULL)
	{
		parent::__construct();
		$this->model = $model;
		$this->userId = $userId;
		$this->tableKey = $tableKey;
		$this->searched = $searched;
	}


	/**
	 * Set as favorite
	 * @param int
	 */
	public function handleFavour($id)
	{
		$item = $this->getItemKey($id);
		if ($this->model->isFree($item)) {
			$this->model->insert($item);
		}

		if ($this->searched) {
			$this->parent->processSearch($this->searched);
		}

		if ($this->useAjax) {
			$this->invalidateControl();
		}
	}


	/**
	 * Unset as favorite
	 * @param int
	 */
	public function handleUnfavour($id)
	{
		$item = $this->getItemKey($id);
		if ($this->model->item($item)) {
			$this->model->delete($item);
		}

		if ($this->searched) {
			$this->parent->processSearch($this->searched);
		}

		if ($this->useAjax) {
			$this->invalidateControl();
		}
	}


	public function render($id)
	{
		$item = $this->getItemKey($id);

		$this->template->isFavorite = $this->model->count($item);

		$this->template->id = $id;
		$this->template->favoriteClass = $this->favoriteClass;
		$this->template->nonFavoriteClass = $this->nonFavoriteClass;
		$this->template->useAjax = $this->useAjax;
	
		$this->template->render();
	}


	/********************* helpers *********************/


	/** 
	 * Get item key
	 * @param int
	 */
	private function getItemKey($id)
	{
		return array(
			"user_id" => $this->userId,
			$this->tableKey => $id
		);
	}

}