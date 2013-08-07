<?php

namespace Schmutzka\Models;

use Nette;


class Event extends Base
{

	/**
	 * Get events for calendar by particular month
	 * @param string
	 */
	public function getForCalendar($month)
	{
		$result = $this->table('display_in_calendar', 1)->where('date LIKE ?', '$month%')->order('date, time');
		return $result;
	}


	/**
	 * Get all front
	 * @param int
	 */
	public function getAllFront($categoryId = NULL)
	{
		$result = $categoryId ? $this->table('event_category_id', $categoryId) : $this->table();
		$result->order('date DESC, time DESC');

		if ($categoryId && $this->db->event_category('id', $categoryId)->fetchSingle('use_expiration')) {
			$datetime = new Nette\DateTime;
			$result->where('date >= ?', $datetime->format('Y-m-d'));
			$result->where('time IS NULL OR time > ?', $datetime->format('H:i:s'));
		}

		return $result;
	}


	/**
	 * Get all actions front
	 * @param int
	 * @special
	 */
	public function getAllNewsFront($newsCategoryId)
	{
		$result = $this->table()->where('event_category_id = ? OR is_news = ?', $newsCategoryId, TRUE);
		$result->order('date DESC, time DESC');

		return $result;
	}

}
