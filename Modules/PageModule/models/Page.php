<?php

namespace Schmutzka\Models;

class Page extends Base
{

	/**
	 * Fetch front
	 */
	public function fetchFront()
	{
		return $this->table("publish_state", "public");
	}	


	/**
	 * Get item front
	 * @param int
	 * @return array|FALSE
	 */
	public function getItemFront($id)
	{
		$result = $this->table("id", $id)
			->where("publish_state", "public");

		if (count($result)) {
			return $result->fetchRow();
		}

		return FALSE;
	}

}
