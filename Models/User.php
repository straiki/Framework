<?php

namespace Schmutzka\Models;


class User extends Base
{

	public function mergeNameAndSurname()
	{
		foreach ($this->fetchAll() as $id => $user) {
			$data['name'] = $user['name'] . ' ' . $user['surname'];
			$this->update($data, $id);
		}
	}


	/**
	 * Order by last value in login column.
	 * @param  array
	 * @return NotORM_Result
	 */
	public function fetchAllOrderBySurname($cond = array())
	{
			return $this->fetchAll($cond)
					->order('LTrim(Reverse(Left(Reverse(login), Locate(" ", Reverse(login))))) ASC');
	}


	/**
	 * Determine and save name and surname from users login
	 */
	public function determineNameAndSurname()
	{
		foreach ($this->fetchAll()->where('name IS NULL AND surname IS NULL') as $row) {
			list($name, $surname) = explode(' ', $row['login']);
			$row['surname'] = $surname;
			$row['name'] = $name;
			$row->update();
		}
	}

}
