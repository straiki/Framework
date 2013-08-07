<?php

namespace Components;

use Schmutzka;
use Schmutzka\Application\UI\Control;


class FlashMessageControl extends Control
{

	public function renderDefault()
	{
		$flashes = $this->parent->template->flashes;
		if ( ! count($flashes)) {
			return NULL;
		}

		if ($this->translator) {
			foreach ($flashes as $key => $row) {
				$flashes[$key]->message = $this->translator->translate($row->message);
			}
		}

		$this->template->flashes = $flashes;
	}

}
