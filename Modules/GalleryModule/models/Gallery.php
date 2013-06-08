<?php

namespace Schmutzka\Models;

class Gallery extends Base
{

	/**
	 * Get item
	 * @param int
	 */
	public function getItem($id)
	{
		$item = parent::item($id);
		$item["files"] = $this->db->gallery_file("gallery_id", $id);
		$firstImage = $item["files"]->fetchRow();
		$item["first_image"] = $firstImage["name"];

		return $item;
	}


	/**
	 * Get all items
	 */
	public function getAll()
	{
		$result = parent::all();
		foreach ($result as $id => $row) {
			$row["files"] = $this->db->gallery_file("gallery_id", $id);
			$firstImage = $row["files"]->fetchRow();
			$row["first_image"] = $firstImage["name"];
		}

		return $result;
	}

}
