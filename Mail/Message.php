<?php

namespace Schmutzka\Mail;

use Nette;

class Message extends Nette\Mail\Message
{

	/**
	 * Sends email
	 */
	public function send()
	{	
		if ($this->getMailer() instanceof \Schmutzka\Diagnostics\DumpMailService) {
			$this->getMailer()->send($this->build());

		} else {
			$headers = $this->getHeaders();
			foreach ($headers["To"] as $to) {
				$this->sendSimple($to, $headers["Subject"], $this->body, $headers);
			}
		}
	}


	/**
	 * Utf8 mail
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @return bool
	 * @see Vrána - tip 687
	 */
	public function sendSimple($to, $subject, $message, $headers = "")
	{
		$headers = "MIME-Version: 1.0"
			. PHP_EOL . "Content-Type: text/plain; charset=utf-8"
			. PHP_EOL . "Content-Transfer-Ecndoing: 8bit"
			. ($headers ? PHP_EOL . $headers : "");

		iconv_set_encoding("internal_econding", "utf-8");
		$subject = iconv_mime_encode("Subject", $subject);
		$subject = substr($subject, strlen("Subject: "));

		return mail($to, $subject, $message, $headers);		
	}

}