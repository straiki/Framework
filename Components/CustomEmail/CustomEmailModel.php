<?php

namespace Models;

class CustomEmail extends Base
{

	/**
	 * Get email info by codename and language
	 * @param string
	 * @param string
	 * @return array
	 */
	public function getEmailTemplate($codename, $lang = NULL)
	{
		$item = $this->table("codename", $codename)->fetchRow();

		if ($lang) {
			$item["subject"] = $item["subject_" . $lang];
			$item["body"] = $item["body_" . $lang];
		}

		return $item;
	}


	/**
	 * Get email list log
	 */
	public function getEmailLog()
	{
		return $this->db->custom_email_log()->order("datetime DESC");
	}


	/**
	 * Insert into about sending message
	 * @param array
	 */
	public function logMessage($values)
	{
		$this->db->custom_email_log()->insert($values);
	}
	
}