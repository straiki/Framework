<?php

namespace Components;

use Schmutzka;

class FlashMessageControl extends Schmutzka\Application\UI\Control
{

	public function render()
	{
		$flashes = $this->parent->template->flashes;
		if ($this->parent->translator) {
			foreach ($flashes as $key => $row) {
				$flashes[$key] = $this->parent->translator->translate($row);
			}
		}

		$this->template->flashes = $flashes;
		$this->template->render();
	}

}
