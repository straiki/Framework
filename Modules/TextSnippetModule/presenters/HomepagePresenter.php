<?php

namespace TextSnippetModule;

use Schmutzka\Application\UI\Module\Presenter;

class HomepagePresenter extends Presenter
{
	/** @inject @var Schmutzka\Models\TextSnippet */
	public $textSnippetModel;

}
