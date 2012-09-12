<?php

namespace Services;

use Nette\Mail\Message;

class CustomEmailSender extends \Schmutzka\Application\UI\Control
{

	/** @var \Nette\DI\Container */
	public $context;

	/** @var array */
	public $logValues = array();


	/** @var \Models\CustomEmail */
	private $model;

	/** @var string */
	private $lang;

	/** @var bool */
	private $logMessages = FALSE;

	/** @var array */
	private $bcc = array();


	public function __construct(\Nette\DI\Container $context) 
	{
		$this->context = $context;
		$this->model = $context->models->customEmail;
	}	


	/**
	 * Send custom mail
	 * @param string
	 * @param array
	 * @param string
	 * @param string
	 */
	public function send($codename, $values = array(), $toEmail, $toName = NULL)
	{	
		$item = $this->model->getEmailTemplate($codename, $this->lang);

		// template
		$template = parent::createTemplateFromFile(__DIR__ . "/blankEmail.latte");

		$replaceArray = array();

		if ($values) {
			foreach ($values as $key => $value) {
				$key = "%" . strtoupper($key) . "%";
				$replaceArray[$key] = $value;
			}
		}

		// debug here!

		$body = strtr($item["body"], $replaceArray);	
		// $template->body = $body;

		$subject = strtr($item["subject"], $replaceArray);
		// $template->subject = $subject;

		
		// use internal mail
		$header = "From: ". $item["from_name"] . " <" . $item["from_email"] . ">";
		mail($toEmail, $subject, $body, $header);


		/*// email
		$mail = new Message;
		$mail->setFrom($item["from_email"], $item["from_name"])
			->addTo($toEmail, $toName)
			->setSubject($subject)
			->setHtmlBody($template);

		if ($this->bcc) {
			$mail->addBcc(implode(", ", $this->bcc));
		}

		// debug
		$mail->addBcc("tomas.vot@gmail.com");

		$mail->send();
		*/

		if ($this->logMessages) {
			$this->logMessage($codename, $toEmail, $toName, $subject, $body, $this->logValues);
		}
	}

	
	/** 
	 * Set lang version
	 * @param string
	 */
	public function useLangVersion($lang)
	{
		$this->lang = $lang;
		return $this;
	}


	/** 
	 * Add bcc
	 * @param string
	 */
	public function addBcc($email)
	{
		$this->bcc[] = $email;
		return $this;
	}


	/********************* Simple message logger *********************/


	/** 
	 * Log message
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @param array
	 */
	private function logMessage($codename, $toEmail, $toName, $subject, $body, $logValues = array())
	{
		$values = array(
			"datetime" => new \DateTime,
			"codename" => $codename,
			"to_email" => $toEmail,
			"to_name" => $toName,
			"subject" => $subject,
			"body" => $body
		);
		$values = array_merge($values, $logValues);

		$this->model->logMessage($values);
	}


	/**
	 * Turn on logging
	 */
	public function activateLogging()
	{
		$this->logMessages = TRUE;
		return $this;
	}

}