<?php

namespace Schmutzka\Application\UI\Module;

use AdminModule;
use Nette;


class Presenter extends AdminModule\BasePresenter
{
	/** @persistent @var int */
	public $id;


	/**
	 * @param  int
	 */
	public function handleDelete($id)
	{
		$this->deleteHelper($this->model, $id);
	}


	/**
	 * @param int
	 */
	public function renderEdit($id)
	{
		$this->loadItemHelper($this->model, $id);
	}


	/**
	 * Sort helper
	 * @param  array
	 * @param string
	 */
	public function handleSort($data, $rankKey = 'rank')
	{
		$data = explode(',', $data);
		$i = 1;
		foreach ($data as $item) {
			$this->model->update(array($rankKey => $i), $item);
			$i++;
		}
	}


	/********************** module helpers **********************/


	/**
	 * @return  Nette\ArrayHash
	 */
	public function getModuleParams()
	{
		return $this->paramService->getModuleParams($this->presenter->module);
	}


	/**
	 * @return  *\Models\*
	 */
	public function getModel()
	{
		$className = $this->getReflection()->getName();
		$classNameParts = explode('\\', $className);

		$name = lcfirst(substr(array_pop($classNameParts), 0, -9));
		if ($name == 'homepage') {
			$name = lcfirst(substr(array_shift($classNameParts), 0, -6));
		}

		$modelName = $name . 'Model';

		if (!property_exists($this, $modelName)) {
			$modelName = lcfirst($this->module) . ucfirst($modelName);
		}

		if (!property_exists($this, $modelName)) {
			$modelName = lcfirst($this->module) . 'Model';
		}

		return $this->{$modelName};
	}

}
