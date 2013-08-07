<?php

namespace Schmutzka\Models;

class Gallery extends Base
{
	/** @inject @var Schmutzka\Models\GalleryFile */
	public $galleryFileModel;


	/**
	 * @param int
	 */
	public function getItem($id)
	{
		$item = parent::item($id);
		$item['files'] = $this->galleryFileModel->fetchAll('gallery_id', $id);
		$firstImage = $item['files']->fetchRow();
		$item['first_image'] = $firstImage['name'];

		return $item;
	}


	/**
	 * Get all items
	 */
	public function getAll()
	{
		$result = parent::all();
		foreach ($result as $id => $row) {
			$row['files'] = $this->db->gallery_file('gallery_id', $id);
			$firstImage = $row['files']->fetchRow();
			$row['first_image'] = $firstImage['name'];
		}

		return $result;
	}


	/********************** helpers **********************/


	/**
	 * @return  array
	 */
	public function getModuleParams()
	{
		return $this->paramService->getModuleParams('gallery');
	}


	/**
	 * @param  array
	 * @return  array
	 */
	private function completeItem($item)
	{
		$item =

		$item['files'] = $this->db->gallery_file('gallery_id', $id);
		$firstImage = $item['files']->fetchRow();
		$item['first_image'] = $firstImage['name'];

		return $result;
	}

}
