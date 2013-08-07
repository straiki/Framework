<?php

namespace Schmutzka\Models;

class File extends Base
{

	/**
	 * Fetch by type
	 * @param string
	 * @param int
	 * @param string
	 * @return NotORM_Result
	 */
	public function fetchByType($type, $keyId, $sort = 'name')
	{
		return $this->table()->where($type . '_id', $keyId)
			->order($sort)
			->fetchPairs('id');
	}

}
