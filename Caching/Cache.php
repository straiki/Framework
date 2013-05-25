<?php

namespace Schmutzka\Caching;

use Nette;

class Cache extends Nette\Caching\Cache
{

	/**
	 * Get cached or load and save to cache using function
	 * @param string
	 * @param callback
	 * @param mixed
	 * @param mixed
	 * @return string|array
	 */
	public function callCache($key, $function, $key, $expire = NULL)
	{
		$data = $this->cache->load($key);
		if ($data === NULL) {
			callback($function);
			/// $data = $this->{$function}($key, $key2, $key3);
			// $data = $this->isTeamInData($key);
			$this->cache->save($key, $data, array(
				"expire" => $expire
			));
		}

		return $data;
	}

}
