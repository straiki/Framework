<?php

namespace Components;

use Schmutzka;

class FlashMessageControl extends Schmutzka\Application\UI\Control
{

	public function render()
	{
		$flashes = array();
		if ($this->translator) {
			foreach ($this->parent->template->flashes as $row) {
				$row->message = $this->translator->translate($row->message);
				$flashes[] = $row;
			}
		}

		$this->template->flashes = $flashes;
		$this->template->render();
	}

}
