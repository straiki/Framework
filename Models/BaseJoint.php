<?php

namespace Schmutzka\Models;

abstract class BaseJoint extends Base
{
	/** @var string */
	protected $mainKeyName;

	/** @var string */
	protected $otherKeyName;


	/**
	 * Fetch by main
	 * @param  int
	 * @param  string|NULL
	 * @return  array
	 */
	public function fetchByMain($id, $secondKey = NULL)
	{
		return $this->table($this->mainKeyName, $id)->fetchPairs($this->otherKeyName, $secondKey ?: $this->otherKeyName);
	}


	/**
	 * Update current data - remove old, add new
	 * @param  int
	 * @param  array
	 */
	public function modify($id, $data)
	{
		$oldItems = $this->table($this->mainKeyName, $id)->fetchPairs($this->otherKeyName);
		$key[$this->mainKeyName] = $id;

		foreach ($data as $otherKey) {
			$key[$this->otherKeyName] = $otherKey;
			if (!isset($oldItems[$otherKey])) {
				$this->insert($key);
			}

			unset($oldItems[$otherKey]);
		}

		foreach ($oldItems as $otherKey) {
			$key[$this->otherKeyName] = $otherKey;
			$this->delete($key);
		}
	}

}
