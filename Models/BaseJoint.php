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
		return $this->table($this->mainKeyName, $id)
			->fetchPairs($this->otherKeyName, $secondKey ?: $this->otherKeyName);
	}


	/**
	 * Update current data - remove old, add new
	 * @param  int
	 * @param  array
	 */
	public function modify($id, $data)
	{
		$oldItems = $this->table($this->mainKeyName, $id)
			->fetchPairs($this->otherKeyName);

		$key[$this->mainKeyName] = $id;

		foreach ($data as $otherKey) {
			$key[$this->otherKeyName] = $otherKey;
			if ( ! isset($oldItems[$otherKey])) {
				$this->insert($key);
			}

			unset($oldItems[$otherKey]);
		}

		foreach ($oldItems as $otherKey) {
			$key[$this->otherKeyName] = $otherKey;
			$this->delete($key);
		}
	}


	/**
	 * Update current data - remove old, add new
	 * @param  int
	 * @param  array
	 */
	public function modifyArrayData($id, $data)
	{
		$oldItemsIds = $this->table($this->mainKeyName, $id)
			->fetchPairs('id', 'id');

		$checkKey[$this->mainKeyName] = $id;

		foreach ($data as $key => $value) {
			$checkKey[$this->otherKeyName] = $key;

			if ($idToRemove = $this->table($checkKey)->fetchSingle('id')) {
				unset($oldItemsIds[$idToRemove]);
				$this->update($value, $idToRemove);

			} else {
				$value[$this->mainKeyName] = $id;
				$this->insert($value);
			}
		}

		foreach ($oldItemsIds as $key) {
			$this->delete($key);
		}
	}

}
