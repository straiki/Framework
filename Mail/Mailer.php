<?php

namespace Schmutzka\Mail;

use Nette;
use Schmutzka;

class Mailer extends Nette\Mail\SendmailMailer
{
	/** @var int */
	private $customEmailId;

	/** @var int */
	private $debugMode;

	/** @var bool */
	private $useLogger = FALSE;

	/** @var array */
	private $loggerData = array();

	/** @var Nette\Session\SessionSection */
	private $dumpMailSession;

	/** @var Schmutzka\Models\CustomEmail */
	private $customEmailModel;

	/** @var Schmutzka\Models\EmailLog */
	private $emailLogModel;


	public function __construct(Schmutzka\ParamService $paramService, Nette\Http\Session $session, Schmutzka\Models\CustomEmail $customEmailModel, Schmutzka\Models\EmailLog $emailLogModel)
	{
		if ($this->debugMode = $paramService->debugMode) {
			$this->dumpMailSession = $session->getSection('dumpMail');
		}

		$this->customEmailModel = $customEmailModel;
		$this->emailLogModel = $emailLogModel;

		if ($paramService->params->cmsSetup->modules->email->useLogger) {
			$this->useLogger = TRUE;
		}
	}


	/**
	 * Send email
	 * @param Nette\Mail\Message
	 */
	public function send(Nette\Mail\Message $message)
	{
		// default headers prevents error
		if (!$message->getHeader('From')) {
			$message->setFrom('example@gmail.com'); // replaced by login email
		}

		// dump bar
		if ($this->debugMode) {
			$i = rand(1,1000);
			$this->dumpMailSession->{$i} = $this->getData($message);
			$this->dumpMailSession->setExpiration('+10 seconds', $i);
		}

		if ($this->useLogger) {
			$this->emailLogModel->insert($this->getData($message, TRUE));
		}

		parent::send($message);
	}


	/**
	 * Use custom template from database
	 * @param string
	 * @param array
	 * @param bool
	 * @return string|array
	 */
	public function getCustomTemplate($uid, array $values = array(), $includeSubject = FALSE)
	{
		$customEmail = $this->customEmailModel->item(array('uid' => $uid));
		if (!$customEmail) {
			throw new \Exception('Record with uid $uid doesn't exist.');
		}
		$this->customEmailId = $customEmail['id'];

		$template = new Nette\Templating\FileTemplate();
		$template->registerFilter(new Nette\Latte\Engine());
		$template->setFile(MODULES_DIR . '/EmailModule/templates/@blankEmail.latte');

		$replaceArray = array();
		foreach ($values as $key => $value) {
			$key = '%' . strtoupper($key) . '%';
			$replaceArray[$key] = $value;
		}

		$body = strtr($customEmail['body'], $replaceArray);
		if (!$includeSubject) {
			return $body;
		}

		$subject = strtr($customEmail['subject'], $replaceArray);
		return array(
			'body' => $body,
			'subject' => $subject
		);
	}


	/********************** setters **********************/


	/**
	 * Turn on logger
	 * @param array
	 */
	public function log($data = array())
	{
		$this->loggerData = $data;
		$this->useLogger = TRUE;
		return $this;
	}


	/********************** helpers **********************/


	/**
	 * Get mail data
	 * @param Nette\Mail\Message
	 * @param bool
	 */
	private function getData(Nette\Mail\Message $message, $db = FALSE)
	{
		$to = $message->getHeader('To');
		$from = $message->getHeader('From');

		$array = array(
			'custom_email_id' => $this->customEmailId,
			'datetime' => new Nette\DateTime,
			'to_email' => key($to),
			'to_name' => array_pop($to),
			'subject' => $message->getHeader('Subject'),
			 'html' => $message->getHtmlBody(),
			 'body' => $message->getBody(),
		);

		if (!$db) {
			$array['to'] = $message->getHeader('To');
			$array['from'] =  $message->getHeader('From');
		}

		$array = array_merge($array, $this->loggerData);
		return $array;
	}

}
