<?php

namespace Schmutzka\Diagnostics;

use Nette\Mail\Message;

class DumpMailService implements \Nette\Mail\IMailer
{

	/** @var \Nette\Session\SessionSection */
	private $session;

	/** @var int */
	public $expiration= 5;
	

	public function __construct(\Nette\Http\Session $session = NULL)
	{
		$this->session = $session->getSection("dumpMail");
	}


	/**
	 * Send a mail to session
	 */
	function send(Message $mail)
	{
		$i = substr(uniqid(), 7,6);
		$this->session->{$i} = $mail; 
		$this->session->setExpiration("+$this->expiration seconds", $i); // hold for x secs
	}

}