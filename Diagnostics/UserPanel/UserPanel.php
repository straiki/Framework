<?php

/**
 * @author Mikuláš Dítě
 * @link https://github.com/Mikulas/UserPanel/blob/master/UserPanel.php
 */

namespace Schmutzka\Diagnostics\Panels;

use Schmutzka\Forms\Form,
	Nette\Diagnostics\Debugger,
	Nette\Security\AuthenticationException,
	Schmutzka\Utils;

class UserPanel extends \Schmutzka\Application\UI\Control implements \Nette\Diagnostics\IBarPanel
{

	/** @var \Nette\Security\User */
	private $user;

	/** @var \Nette\Http\Session */
	private $session;

	/** @var \Nette\DI\Container */
	public $context;

	/** @var array */
	private $credentials = array();


	/** @var string */
	public $usernameColumn = "name";
	

	public function __construct(\Nette\Security\User $user, \Nette\Http\Session $session, \Nette\DI\Container $context)
	{
		$this->session = $session;
 		$this->context = $context;
		$this->user = $user;

		parent::__construct($this->context->application->getPresenter(), "userPanel");
	}


	/**
	 * Renders HTML code for custom tab
	 * IDebugPanel
	 * @return void
	 */
	public function getTab()
	{
		$data = $this->getData();
		return "<img src='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAnpJREFUeNqEU19IU1EY/927e52bWbaMQLbJwmgP0zIpffDFUClsyF56WJBQkv1RyJeo2IMPEghRQeAIoscegpBqTy6y3CDwrdzDwjCVkdqmzT+7u//O1jm3knkV/MF3z3e+8zu/7zv3O4crFotgaHC7jfHrwgKuBYPtVqt1BBx3SlNV5HK5KSmXu/N6fPxTKY+BMwvUNzY22cvFz6TIi0TXoWkaFEWBrkra+rrUtJLJTJcKCDCBZrqvyBaRCTMBnRCwKhRZFlVFuUspl0r5OwRUKXu+opxgsP8qfE4Bmk7wZV7Bg5FRqIR0m/m8OfA7K9n6bt1GvbeWlq2CKxCcPnEM1wf6sZknFXsKDF+c+dHgVKBmf4JoqmHMb/Va8OTK4vSeAhThpW9vwdsPociJ1ATD/zU7bqyZyVtdKMWHIXH0SJ3/RrWn05hn5t5jeeZN+OyQdtPMFbA77i1/f9dE7cy/+RS10G7EbRX4fL42OvQGAoFgT6uM2uPnjHhq9iNeTABjY2Mv6fR5IpGY2Cbg9XqPUr/PZrMNOJ1Oq65pfCQSwcPwK1TtE9F7OYCurgsQRbGQSqWUfD7/lPKfJZPJWc7j8ZzkeX7S5XLZHA6HIEkSqBCam5uxYqnDwf02WDeTiMVikGUZdrsdq6urOhWSCSGdFhoIud3ulrKyMiGbzRrXVqX9j8fj8Pu7UXO4EiPDIZYdNDN7F6DvhKf7+HQ6bRGoaju970bm/2CZmCXn0nAcyBn+xsbG1joTooJsbxv71LDNhUJh299lpPnFNaxt/hVjlZWCPTIar+YEQXhEzzxobk9HRyeWrC2oqhRRnplENBrd0UKa5PEfAQYAH6s95RSa3ooAAAAASUVORK5CYII='>" . 
		($this->user->loggedIn ? "<span style='margin: 0; padding: 0;'>" . $this->getUsername() . "</span>" : "Guest");
	}


	/**
	 * Renders HTML code for custom panel
	 * IDebugPanel
	 * @return void
	 */
	public function getPanel()
	{
		$template = parent::createTemplate();

		if ($this->user->loggedIn) {
			$this["loginForm-user"]->setDefaultValue($this->getUsername());
		} 

		$template->user = $this->user;
		$template->data = $this->getData();
		$template->username = $this->getUsername();

		if ($this->session->hasSection("Nette.Http.UserStorage/")) {
			$template->userSession = iterator_to_array($this->session->getSection("Nette.Http.UserStorage/"));
		}

		return $template;
	}


	/**
	 * Registers panel to Debug bar
	 * @return UserPanel
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
	private function getUsername()
	{
		$data = $this->getData();
		$username = isset($data[$this->usernameColumn]) ? $data[$this->usernameColumn] : NULL;

		return $username;
	}


	/**
	 * $user->identity->data
	 * @return array
	 */
	private function getData()
	{
		if (method_exists($this->user->identity, "getData")) {
			return $this->user->identity->data;
		}

		return array();
	}


	/**
	 * Add item to user list
	 * @param string
	 * @param string
	 */
	public function addUser($username, $password)
	{
		$this->credentials[$username] = $password;

		return $this;
	}


	/**
	 * Returns value => name arrray for filling radio list and add __guest
	 * Original array in $this->credentials is not used as this prevents
	 * sending passwords to the browser
	 * @return array
	 */
	private function getCredentialsData()
	{
		$data["__guest"] = "guest";
		foreach ($this->credentials as $username => $password) {
			$data[$username] = $username;
		}

		return $data;
	}


	/**
	 * Sign in form
	 */
	public function createComponentLoginForm()
	{
		$form = new Form;
		$form->addSelect("user", "Active role:", $this->getCredentialsData())
			->setAttribute("class","onChangeSubmit");

		return $form;
	}


	/**
	 * @param Form $form
	 */
	public function loginFormSent(Form $form)
	{
		try {
			$values = $form->values;
			$username = $values["user"];
			if ($username == "__guest") {
				$this->user->logout(TRUE);
			}
			else {
				$password = $this->credentials[$username];
				$this->context->user->login($username, $password);
			}
		}
		catch (AuthenticationException $e) {
			$this->flashMessage($e->getMessage(), "flash-error");
		}

		$this->redirect("this");
	}

}