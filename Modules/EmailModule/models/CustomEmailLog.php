<?php

namespace Schmutzka\Models;

class EmailLog extends Base
{

	/**
	 * Get email list log
	 */
	public function getAll()
	{
		return $this->fetchAll()->order("datetime DESC");
	}
	
}
