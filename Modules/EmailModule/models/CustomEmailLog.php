<?php

namespace Schmutzka\Models;

class EmailLog extends Base
{

	/**
	 * Insert custom email log
	 */


	/**
	 * Insert normal email log
	 */


	/**
	 * Get email list log
	 */
	public function getAll()
	{
		return $this->all()->order("datetime DESC");
	}
	
}