<?php

namespace Components;

use Schmutzka;

class FlashMessageControl extends Schmutzka\Application\UI\Control
{

	public function render()
	{
		$flashes = $this->parent->template->flashes;
		if ($this->translator) {
			foreach ($flahes as $row) {
				$flashes[$key]->message = $this->translator->translate($row->message);
			}
		}

		$this->template->flashes = $flashes;
		$this->template->render();
	}

}
