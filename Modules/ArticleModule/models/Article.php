<?php

namespace Schmutzka\Models;

use Nette;

class Article extends Base
{

	/**
	 * Fetch front
	 */
	public function fetchFront()
	{
		return $this->table("publish_state", "public")
			->select("article.*, gallery_file.name as titlePhoto")
			->where("publish_datetime <= ? OR publish_datetime IS NULL", new Nette\DateTime)
			->order("publish_datetime DESC");
	}	


	/**
	 * Get item front
	 * @param int
	 * @return array|FALSE
	 */
	public function getItemFront($id)
	{
		$result = $this->table("id", $id)
			->select("article.*, gallery_file.name as titlePhoto")
			->where("publish_state", "public")
			->where("publish_datetime <= ? OR publish_datetime IS NULL", new Nette\DateTime);

		if (count($result)) {
			return $result->fetchRow();
		}

		return FALSE;
	}

}
