<?php

namespace Schmutzka\Models;

class User extends Base
{

	public function mergeNameAndSurname()
	{
		foreach ($this->fetchAll() as $id => $user) {
			$data["name"] = $user["name"] . " " . $user["surname"];
			$this->update($data, $id);
		}
	}

}
