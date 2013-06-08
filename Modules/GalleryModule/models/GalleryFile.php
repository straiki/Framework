<?php

namespace Schmutzka\Models;

class GalleryFile extends Base
{

	/**
	 * @param  int $galleryId
	 * @return array { [ id => name ] }
	 */
	public function fetchOrderedListByGallery($galleryId)
	{
		return $this->table("gallery_id", $galleryId)
			->order("rank")
			->fetchPairs("id", "name");
	}

}