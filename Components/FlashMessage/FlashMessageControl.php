<?php

namespace Components;

use Nette\Utils\Html;

class FlashMessageControl extends \Nette\Application\UI\Control
{
	public function render($el = "div", $elWrap = NULL)
	{
		$flashes = $this->parent->getTemplate()->flashes;

		if($flashes) {
			// automatic setup for lists if $elWrap not set
			$elWrap = ((!$elWrap AND $el == "li") ? "ul" : $elWrap);

			$render = Html::el($elWrap);

			foreach($flashes as $flash) {
				$flashMessage = Html::el($el);
				$flashMessage->class = array("flash", $flash->type ? $flash->type : NULL);
				$flashMessage->setText($flash->message);

				$render->add($flashMessage);
			}

			echo $render;
		}
	}

}