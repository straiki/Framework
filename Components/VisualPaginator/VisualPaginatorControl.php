<?php

use Nette\Application\UI\Control;
use Nette\Utils\Paginator;


/**
 * @author David Grudl
 */
class VisualPaginator extends Control
{
	/** @persistent @var int */
	public $page = 1;

	/** @var Paginator */
	private $paginator;


	/**
	 * @return Nette\Paginator
	 */
	public function getPaginator()
	{
		if ( ! $this->paginator) {
			$this->paginator = new Paginator;
		}

		return $this->paginator;
	}


	public function render()
	{
		$paginator = $this->getPaginator();
		$page = $paginator->page;
		if ($paginator->pageCount < 2) {
			$steps = array($page);

		} else {
			$arr = range(max($paginator->firstPage, $page - 2), min($paginator->lastPage, $page + 2));
			$count = 2;
			$quotient = ($paginator->pageCount - 1) / $count;

			for ($i = 0; $i <= $count; $i++) {
				$arr[] = round($quotient * $i) + $paginator->firstPage;
			}

			sort($arr);
			$steps = array_values(array_unique($arr));
		}

		$this->template->steps = $steps;
		$this->template->paginator = $paginator;
	}


	/**
	 * Loads state informations.
	 * @param  array
	 * @return void
	 */
	public function loadState(array $params)
	{
		parent::loadState($params);
		$this->getPaginator()->page = $this->page;
	}

}
