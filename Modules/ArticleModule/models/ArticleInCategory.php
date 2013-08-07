<?php

namespace Schmutzka\Models;


class ArticleInCategory extends BaseJoint
{
	/** @var string */
	protected $mainKeyName = 'article_id';

	/** @var string */
	protected $otherKeyName = 'article_category_id';

}
