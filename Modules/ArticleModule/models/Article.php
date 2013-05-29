<?php

namespace Schmutzka\Models;

use Nette;

class Article extends Base
{
	/** @inject @var Schmutzka\Config\ParamService */
	public $paramService;


	/**
	 * Fetch front
	 */
	public function fetchFront()
	{
		$result = $this->table("publish_state", "public")
			->select("article.*, gallery_file.name as titlePhoto, CONCAT(user.name, ' ', user.surname) AS authorName");

		if ($this->paramService->getModuleParams("article")->publish_datetime) {
			return $result->where("publish_datetime <= ? OR publish_datetime IS NULL", new Nette\DateTime)
				->order("publish_datetime DESC");

		} else {
			return $result->order("id DESC");
		}

	}


	/**
	 * Get item front
	 * @param int
	 * @return array|FALSE
	 */
	public function getItemFront($id)
	{
		$result = $this->table("article.id", $id)
			->select("article.*, gallery_file.name as titlePhoto");

		if ($this->paramService->getModuleParams("article")->publish_state) {
			$result->where("publish_state", "public");
		}



		return FALSE;
	}

}
