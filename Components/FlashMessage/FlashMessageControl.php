<?php

namespace Components;

use Schmutzka;


class FlashMessageControl extends Schmutzka\Application\UI\Control
{

	public function render()
	{
		$flashes = $this->parent->template->flashes;
		if (!count($flashes)) {
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
