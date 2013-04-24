<?php

namespace Schmutzka\Models;

use Schmutzka\Utils\Name;
use Nette;
use NotORM;

abstract class BaseJoint extends Base
{
	/** @var Schmutzka\Models\* first model */
	protected $firstModel;

	/** @var Schmutzka\Models\* second model */
	protected $secondModel;

	
}
