<?php

namespace Schmutzka\Models;

class File extends Base
{

	/**
	 * Fetch by type
	 * @param string
	 * @param int
	 * @param string
	 */
	public function fetchByType($type, $keyId, $sort = "name")
	{
		return $this->table("type", $type)->where("key_id", $keyId)->order($sort)->fetchPairs("id");
	}

}
