<?php

/**
 * UserPanel
 * @author Mikuláš Dítě
 * @link https://github.com/Mikulas/UserPanel/blob/master/UserPanel.php
 */

namespace Schmutzka\Diagnostics\Panels;

use Nette\Application\UI\Form,
	 Nette\Application\UI\Control,
	 Nette\Diagnostics\Debugger,
	 Nette\Environment,
	 Nette\Diagnostics\IBarPanel,
	 Nette\Security\AuthenticationException,
	 Nette\Latte\Engine;


class User extends Control implements IBarPanel
{

	/** @var \Nette\Security\User */
	private $user;

	/** @var \Nette\Http\Session */
	private $session;

	/** @var \Container */
	private $context;

	/** @var array username => password */
	private $credentials = array();

	/** @var string */
	private $userColumn = 'username';


	public function __construct(\Nette\Security\User $user, \Nette\Http\Session $session, $context)
	{
		$this->session = $session;
 		$this->context = $context;
		$this->user = $user;

		parent::__construct($this->context->application->presenter, "UserPanel");
	}


	/**
	 * Renders HTML code for custom tab
	 * IDebugPanel
	 * @return void
	 */
	public function getTab()
	{
		$data = $this->getData();

		return '<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAnpJREFUeNqEU19IU1EY/927e52bWbaMQLbJwmgP0zIpffDFUClsyF56WJBQkv1RyJeo2IMPEghRQeAIoscegpBqTy6y3CDwrdzDwjCVkdqmzT+7u//O1jm3knkV/MF3z3e+8zu/7zv3O4crFotgaHC7jfHrwgKuBYPtVqt1BBx3SlNV5HK5KSmXu/N6fPxTKY+BMwvUNzY22cvFz6TIi0TXoWkaFEWBrkra+rrUtJLJTJcKCDCBZrqvyBaRCTMBnRCwKhRZFlVFuUspl0r5OwRUKXu+opxgsP8qfE4Bmk7wZV7Bg5FRqIR0m/m8OfA7K9n6bt1GvbeWlq2CKxCcPnEM1wf6sZknFXsKDF+c+dHgVKBmf4JoqmHMb/Va8OTK4vSeAhThpW9vwdsPociJ1ATD/zU7bqyZyVtdKMWHIXH0SJ3/RrWn05hn5t5jeeZN+OyQdtPMFbA77i1/f9dE7cy/+RS10G7EbRX4fL42OvQGAoFgT6uM2uPnjHhq9iNeTABjY2Mv6fR5IpGY2Cbg9XqPUr/PZrMNOJ1Oq65pfCQSwcPwK1TtE9F7OYCurgsQRbGQSqWUfD7/lPKfJZPJWc7j8ZzkeX7S5XLZHA6HIEkSqBCam5uxYqnDwf02WDeTiMVikGUZdrsdq6urOhWSCSGdFhoIud3ulrKyMiGbzRrXVqX9j8fj8Pu7UXO4EiPDIZYdNDN7F6DvhKf7+HQ6bRGoaju970bm/2CZmCXn0nAcyBn+xsbG1joTooJsbxv71LDNhUJh299lpPnFNaxt/hVjlZWCPTIar+YEQXhEzzxobk9HRyeWrC2oqhRRnplENBrd0UKa5PEfAQYAH6s95RSa3ooAAAAASUVORK5CYII=">' .
			($this->user->isLoggedIn() ? '<span style="margin: 0; padding: 0;">' . $this->getUsername() . '</span>' : 'Guest');
	}


	/**
	 * Renders HTML code for custom panel
	 * IDebugPanel
	 * @return void
	 */
	public function getPanel()
	{
		ob_start();
		$template = parent::getTemplate();

		$data = $this->getData();

		$form = $this->getComponent('login');
		if ($this->user->isLoggedIn()) {
			$form['user']->setDefaultValue(strToLower($this->getUsername()));
		} else {
			$form['user']->setDefaultValue('__guest');
		}

		$template->setFile(__DIR__ . "/panel.latte");

		$template->registerFilter(new Engine());
		$template->user = $this->user;
		$template->data = $data;

		// user is logged under session

		if($this->session->hasSection("Nette.Http.UserStorage/")) {
			$userSession = iterator_to_array($this->session->getSection("Nette.Http.UserStorage/"));

			if(isset($userSession["expireTime"])) {
				$template->expireTime = $userSession["expireTime"];
			}
			if(isset($userSession["authTime"])) {
				$template->authTime = $userSession["authTime"];
			}
		}

		$template->userColumn = $this->userColumn;
		$template->username = $this->getUsername();
		$template->render();

		return ob_get_clean();
	}


	/**
	 * IDebugPanel
	 * @return string
	 */
	public function getId()
	{
		return __CLASS__;
	}


	/**
	 * Registers panel to Debug bar
	 * @return UserPanel;
	 */
	public static function register($user, $session, $context)
	{
		$panel = new self($user, $session, $context);
		Debugger::addPanel($panel);
		return $panel;
	}


	/**
	 * Username from user->identity->data from column set via setNameColumn()
	 * @return string|NULL
	 */
	public function getUsername()
	{
		$data = $this->getData();
		return isset($data[$this->userColumn]) ? $data[$this->userColumn] : NULL;
	}


	/**
	 * $user->identity->data
	 * @return array
	 */
	private function getData()
	{
		if (method_exists($this->user->identity, 'getData')) {
			return $this->user->identity->data;
		}
		return array();
	}


	/**
	 * @param string $username default value
	 * @param string $password default value
	 * @return UserPanel provides fluent interface
	 */
	public function addCredentials($username, $password = NULL)
	{
		if(is_null($password)) {
			$password = $username;
		}
		$this->credentials[$username] = $password;
		return $this;
	}


	/**
	 * Sets which $user->identity->data column is supposed to be username
	 * @param string $column
	 * @return UserPanel provides fluent interface
	 */
	public function setNameColumn($column)
	{
		$this->userColumn = $column;
		return $this;
	}


	/**
	 * Returns value => name arrray for filling radio list and add __guest
	 * Original array in $this->credentials is not used as this prevents
	 * sending passwords to the browser
	 * @return array
	 */
	public function getCredentialsRadioData()
	{
		$data = array();
		foreach ($this->credentials as $username => $password) {
			$data[$username] = $username;
		}
		$data['__guest'] = 'guest';
		return $data;
	}


	/**
	 * Sign in form component factory.
	 * @return Nette\Application\AppForm
	 */
	public function createComponentLogin($name)
	{
		$form = new Form($this, $name);

		$form->addRadioList('user', "Active role:", $this->getCredentialsRadioData())
			->setAttribute('class', 'onClickSubmit');

		$form->addSubmit('send', 'Log in');

		$form->onSuccess[] = callback($this, 'onLoginSubmitted');
		return $form;
	}


	/**
	 * @param Form $form
	 */
	public function onLoginSubmitted(Form $form)
	{
		try {
			$values = $form->getValues();
			$username = $values['user'];
			if ($username == '__guest') {
				$this->user->logout(TRUE);
			} else {
				$password = $this->credentials[$username];

				$loginField = "email";
				if(isset($this->context->params["loginField"])) {
					$loginField = $this->context->params["loginField"];
				}
				Environment::getUser()->login($username, $password, $loginField);
			}

			$this->redirect('this');
		} catch (AuthenticationException $e) {
			Environment::getApplication()->presenter->flashMessage($e->getMessage(), 'error');
			$this->redirect('this');
		}
	}
}