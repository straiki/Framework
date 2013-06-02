<?php

namespace Schmutzka\Models;

class User extends Base
{

	public function mergeNameAndSurname()
	{
		foreach ($this->all() as $id => $user) {
			$data["name"] = $user["name"] . " " . $user["surname"];
			$this->update($data, $id);
		}
	}

}
