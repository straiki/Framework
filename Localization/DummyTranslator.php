<?php

namespace Schmutzka\Localization;

use Nette\Localization\ITranslator;

class DummyTranslator implements ITranslator
{

	function translate($message, $count = NULL)
	{
		return $message;
	}

}
